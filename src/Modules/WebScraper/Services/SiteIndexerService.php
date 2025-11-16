<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Models\WebscraperPage;
use Modules\WebScraper\Models\WebscraperChunk;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * SiteIndexerService
 *
 * Indexes web pages for RAG (Retrieval-Augmented Generation):
 * 1. Scrapes page content using WebScraperService
 * 2. Chunks text into manageable pieces
 * 3. Generates embeddings for each chunk using EmbeddingService
 * 4. Stores in MongoDB Atlas for vector search
 */
class SiteIndexerService
{
    protected WebScraperService $scraperService;
    protected EmbeddingService $embeddingService;

    // Chunking configuration
    protected int $chunkSize = 800; // words per chunk
    protected int $chunkOverlap = 100; // words overlap between chunks

    public function __construct(
        WebScraperService $scraperService,
        EmbeddingService $embeddingService
    ) {
        $this->scraperService = $scraperService;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Index a single URL (scrape + chunk + embed + save)
     *
     * @param string $url URL to index
     * @param int|null $ttlDays Time-to-live in days (null = never expires)
     * @return WebscraperPage The indexed page
     * @throws Exception
     */
    public function indexUrl(string $url, ?int $ttlDays = 30): WebscraperPage
    {
        Log::channel('webscraper')->info('SiteIndexer: Starting indexing', [
            'url' => $url,
            'ttl_days' => $ttlDays,
        ]);

        // Check if page already exists and is not expired
        $urlHash = WebscraperPage::generateUrlHash($url);
        $existingPage = WebscraperPage::where('url_hash', $urlHash)->first();

        if ($existingPage && !$existingPage->isExpired()) {
            Log::channel('webscraper')->info('SiteIndexer: Page already indexed and not expired', [
                'url' => $url,
                'indexed_at' => $existingPage->indexed_at,
                'expires_at' => $existingPage->expires_at,
            ]);
            return $existingPage;
        }

        // Create or update page record
        $page = $existingPage ?? new WebscraperPage();
        $page->url = $url;
        $page->url_hash = $urlHash;
        $page->domain = WebscraperPage::extractDomain($url);
        $page->status = 'processing';
        $page->last_scraped_at = now();
        $page->expires_at = $ttlDays ? now()->addDays($ttlDays) : null;
        $page->save();

        try {
            // Step 1: Scrape the page
            Log::channel('webscraper')->info('SiteIndexer: Scraping page', ['url' => $url]);
            $scrapedData = $this->scraperService->scrapeSingleUrl($url);

            // Update page with scraped data
            $page->title = $scrapedData['metadata']['title'] ?? '';
            $page->description = $scrapedData['metadata']['description'] ?? '';
            $page->content = $scrapedData['content']['main'] ?? '';
            $page->raw_html = $scrapedData['raw_html'] ?? '';
            $page->metadata = [
                'scraped_at' => now()->toIso8601String(),
                'scraper_version' => '1.0',
            ];
            $page->word_count = str_word_count($page->content);
            $page->save();

            // Step 2: Delete old chunks if re-indexing
            if ($existingPage) {
                Log::channel('webscraper')->info('SiteIndexer: Deleting old chunks', ['page_id' => $page->_id]);
                WebscraperChunk::where('page_id', $page->_id)->delete();
            }

            // Step 3: Chunk the content
            Log::channel('webscraper')->info('SiteIndexer: Chunking content', [
                'word_count' => $page->word_count,
                'chunk_size' => $this->chunkSize,
            ]);
            $chunks = $this->chunkText($page->content);

            // Step 4: Generate embeddings and save chunks
            Log::channel('webscraper')->info('SiteIndexer: Generating embeddings', [
                'chunk_count' => count($chunks),
            ]);

            foreach ($chunks as $index => $chunkText) {
                // Generate embedding for this chunk
                $embedding = $this->embeddingService->generateEmbedding($chunkText);

                // Save chunk with embedding
                $chunk = new WebscraperChunk();
                $chunk->page_id = $page->_id;
                $chunk->content = $chunkText;
                $chunk->chunk_index = $index;
                $chunk->word_count = str_word_count($chunkText);
                $chunk->embedding = $embedding;
                $chunk->chunk_hash = WebscraperChunk::generateChunkHash($chunkText);
                $chunk->metadata = [
                    'created_at' => now()->toIso8601String(),
                ];
                $chunk->save();

                Log::channel('webscraper')->debug('SiteIndexer: Chunk saved', [
                    'page_id' => $page->_id,
                    'chunk_index' => $index,
                    'word_count' => $chunk->word_count,
                ]);
            }

            // Step 5: Mark page as indexed
            $page->chunk_count = count($chunks);
            $page->markAsIndexed();

            Log::channel('webscraper')->info('SiteIndexer: Indexing completed successfully', [
                'url' => $url,
                'page_id' => $page->_id,
                'chunk_count' => count($chunks),
                'word_count' => $page->word_count,
            ]);

            return $page;

        } catch (Exception $e) {
            Log::channel('webscraper')->error('SiteIndexer: Indexing failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $page->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Index multiple URLs
     *
     * @param array $urls Array of URLs to index
     * @param int|null $ttlDays Time-to-live in days
     * @return array Array of indexed pages
     */
    public function indexUrls(array $urls, ?int $ttlDays = 30): array
    {
        $indexedPages = [];

        foreach ($urls as $url) {
            try {
                $indexedPages[] = $this->indexUrl($url, $ttlDays);
            } catch (Exception $e) {
                Log::channel('webscraper')->warning('SiteIndexer: Failed to index URL', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
                // Continue with other URLs
            }
        }

        return $indexedPages;
    }

    /**
     * Chunk text into overlapping segments
     *
     * @param string $text Text to chunk
     * @return array Array of text chunks
     */
    protected function chunkText(string $text): array
    {
        if (empty(trim($text))) {
            return [];
        }

        // Split into words
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $totalWords = count($words);

        if ($totalWords === 0) {
            return [];
        }

        // If text is smaller than chunk size, return as single chunk
        if ($totalWords <= $this->chunkSize) {
            return [$text];
        }

        $chunks = [];
        $startIndex = 0;

        while ($startIndex < $totalWords) {
            // Extract chunk
            $chunkWords = array_slice($words, $startIndex, $this->chunkSize);
            $chunkText = implode(' ', $chunkWords);
            $chunks[] = $chunkText;

            // Move to next chunk with overlap
            $startIndex += ($this->chunkSize - $this->chunkOverlap);

            // Prevent infinite loop
            if ($startIndex >= $totalWords) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Re-index expired pages
     *
     * @param int $limit Maximum number of pages to re-index
     * @return int Number of pages re-indexed
     */
    public function reindexExpiredPages(int $limit = 10): int
    {
        $expiredPages = WebscraperPage::expired()
            ->limit($limit)
            ->get();

        $count = 0;
        foreach ($expiredPages as $page) {
            try {
                $this->indexUrl($page->url);
                $count++;
            } catch (Exception $e) {
                Log::channel('webscraper')->warning('SiteIndexer: Failed to re-index expired page', [
                    'url' => $page->url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Get indexing statistics
     *
     * @return array Statistics about indexed pages
     */
    public function getStats(): array
    {
        $totalPages = WebscraperPage::count();
        $indexedPages = WebscraperPage::indexed()->count();
        $failedPages = WebscraperPage::where('status', 'failed')->count();
        $totalChunks = WebscraperChunk::count();

        return [
            'total_pages' => $totalPages,
            'indexed_pages' => $indexedPages,
            'failed_pages' => $failedPages,
            'processing_pages' => $totalPages - $indexedPages - $failedPages,
            'total_chunks' => $totalChunks,
            'avg_chunks_per_page' => $indexedPages > 0 ? round($totalChunks / $indexedPages, 2) : 0,
        ];
    }
}