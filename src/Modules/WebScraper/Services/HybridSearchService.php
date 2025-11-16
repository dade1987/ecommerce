<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Services\QueryEnhancers\DomainContextEnhancer;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * HybridSearchService
 *
 * Combines Vector Search (semantic similarity) with Text Search (keyword matching)
 * using Reciprocal Rank Fusion (RRF) algorithm.
 *
 * RRF Formula: score = Σ(1 / (k + rank))
 * where k = 60 (standard constant)
 *
 * Benefits:
 * - No need to normalize scores from different search systems
 * - Industry-standard algorithm (used by Elasticsearch, etc.)
 * - Better results by combining semantic understanding + keyword matching
 *
 * Use Cases:
 * - RAG Q&A with typo tolerance
 * - E-commerce product search
 * - Document retrieval with fuzzy matching
 */
class HybridSearchService
{
    /**
     * RRF constant
     */
    protected int $k;

    /**
     * Result weights
     */
    protected float $vectorWeight;
    protected float $textWeight;

    /**
     * Search services
     */
    protected ClientSiteQaService $qaService;
    protected SearchIndexService $searchService;

    /**
     * Constructor - automatically configure query enhancement strategy
     */
    public function __construct(
        ClientSiteQaService $qaService,
        SearchIndexService $searchService
    ) {
        $this->qaService = $qaService;
        $this->searchService = $searchService;

        // Load configuration
        $this->k = config('webscraper.rag.hybrid.rrf_k', 60);
        $this->vectorWeight = config('webscraper.rag.hybrid.vector_weight', 1.0);
        $this->textWeight = config('webscraper.rag.hybrid.text_weight', 1.0);

        // Configure DomainContextEnhancer strategy for both search services
        $this->configureDomainEnhancer();
    }

    /**
     * Configure domain context enhancer for search services
     */
    protected function configureDomainEnhancer(): void
    {
        $enhancer = new DomainContextEnhancer();

        // Both services will use the same enhancement strategy
        // This ensures consistent query enhancement across vector + text search
        $this->qaService->setQueryEnhancer($enhancer);
        $this->searchService->setQueryEnhancer($enhancer);
    }

    /**
     * Perform hybrid search combining vector + text search
     *
     * @param string $query Search query
     * @param string|null $domain Filter by domain
     * @param array $options Search options
     * @return array Merged results with RRF scores
     * @throws Exception
     */
    public function search(
        string $query,
        ?string $domain = null,
        array $options = []
    ): array {
        Log::channel('webscraper')->info('HybridSearch: Starting hybrid search', [
            'query' => $query,
            'domain' => $domain,
            'options' => $options,
        ]);

        $startTime = microtime(true);

        try {
            // Extract options (use config defaults if not provided)
            $topK = $options['topK'] ?? 10;
            $vectorWeight = $options['vector_weight'] ?? $this->vectorWeight;
            $textWeight = $options['text_weight'] ?? $this->textWeight;

            // 1. Vector Search (semantic similarity)
            $vectorResults = $this->performVectorSearch($query, $domain, $topK);

            // 2. Text Search (keyword matching with fuzzy)
            $textResults = $this->performTextSearch($query, $domain, $topK);

            // 3. Apply Reciprocal Rank Fusion
            $mergedResults = $this->reciprocalRankFusion(
                vectorResults: $vectorResults,
                textResults: $textResults,
                k: $this->k,
                vectorWeight: $vectorWeight,
                textWeight: $textWeight
            );

            // 4. Limit to topK
            $finalResults = array_slice($mergedResults, 0, $topK);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::channel('webscraper')->info('HybridSearch: Search completed', [
                'results_count' => count($finalResults),
                'duration_ms' => $duration,
                'vector_count' => count($vectorResults),
                'text_count' => count($textResults),
            ]);

            return [
                'query' => $query,
                'domain' => $domain,
                'results' => $finalResults,
                'count' => count($finalResults),
                'method' => 'hybrid_rrf',
                'stats' => [
                    'vector_results' => count($vectorResults),
                    'text_results' => count($textResults),
                    'merged_results' => count($mergedResults),
                    'final_results' => count($finalResults),
                    'duration_ms' => $duration,
                ],
            ];

        } catch (Exception $e) {
            Log::channel('webscraper')->error('HybridSearch: Search failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Hybrid search failed: ' . $e->getMessage());
        }
    }

    /**
     * Perform vector search using ClientSiteQaService
     *
     * @param string $query Search query
     * @param string|null $domain Filter by domain
     * @param int $topK Number of results
     * @return array Vector search results
     */
    protected function performVectorSearch(
        string $query,
        ?string $domain,
        int $topK
    ): array {
        try {
            // Use injected service (already has enhancer configured)
            $results = $this->qaService->searchChunks(
                query: $query,
                domain: $domain,
                topK: $topK
            );

            Log::channel('webscraper')->debug('HybridSearch: Vector search completed', [
                'count' => count($results),
            ]);

            return $results;

        } catch (Exception $e) {
            Log::channel('webscraper')->warning('HybridSearch: Vector search failed, continuing with text only', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Perform text search using SearchIndexService
     *
     * @param string $query Search query
     * @param string|null $domain Filter by domain
     * @param int $topK Number of results
     * @return array Text search results
     */
    protected function performTextSearch(
        string $query,
        ?string $domain,
        int $topK
    ): array {
        try {
            // Use injected service (already has enhancer configured)
            $response = $this->searchService->search(
                collection: 'webscraper_chunks',
                query: $query,
                options: [
                    'domain' => $domain,
                    'fuzzy' => true,
                    'limit' => $topK,
                    'fields' => ['content'],
                ]
            );

            Log::channel('webscraper')->debug('HybridSearch: Text search completed', [
                'count' => $response['count'],
            ]);

            return $response['results'];

        } catch (Exception $e) {
            Log::channel('webscraper')->warning('HybridSearch: Text search failed, continuing with vector only', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Apply Reciprocal Rank Fusion algorithm
     *
     * Formula: RRF_score(d) = Σ(weight / (k + rank(d)))
     *
     * @param array $vectorResults Results from vector search
     * @param array $textResults Results from text search
     * @param int $k RRF constant (default 60)
     * @param float $vectorWeight Weight for vector results (default 1.0)
     * @param float $textWeight Weight for text results (default 1.0)
     * @return array Merged and ranked results
     */
    protected function reciprocalRankFusion(
        array $vectorResults,
        array $textResults,
        int $k = 60,
        float $vectorWeight = 1.0,
        float $textWeight = 1.0
    ): array {
        $scores = [];
        $documents = [];

        // Process vector search results
        foreach ($vectorResults as $rank => $result) {
            $url = $this->extractUrl($result);

            if (!$url) {
                continue;
            }

            // Calculate RRF score
            $rrfScore = $vectorWeight / ($k + $rank + 1);

            if (!isset($scores[$url])) {
                $scores[$url] = 0;
                $documents[$url] = $this->normalizeDocument($result, 'vector');
            }

            $scores[$url] += $rrfScore;

            // Store original vector score for debugging
            $documents[$url]['vector_score'] = $result['score'] ?? null;
            $documents[$url]['vector_rank'] = $rank + 1;
        }

        // Process text search results
        foreach ($textResults as $rank => $result) {
            $url = $this->extractUrl($result);

            // DEBUG: Log each text result URL
            Log::channel('webscraper')->debug('HybridSearch: Processing text result', [
                'rank' => $rank,
                'url' => $url,
                'has_url' => !empty($url),
                'result_keys' => is_array($result) ? array_keys($result) : 'not_array',
            ]);

            if (!$url) {
                Log::channel('webscraper')->warning('HybridSearch: Text result has no URL, skipping', [
                    'rank' => $rank,
                    'result' => $result,
                ]);
                continue;
            }

            // Calculate RRF score
            $rrfScore = $textWeight / ($k + $rank + 1);

            if (!isset($scores[$url])) {
                $scores[$url] = 0;
                $documents[$url] = $this->normalizeDocument($result, 'text');
                Log::channel('webscraper')->debug('HybridSearch: New URL from text search', [
                    'url' => $url,
                    'rank' => $rank,
                ]);
            } else {
                Log::channel('webscraper')->debug('HybridSearch: URL already exists (merging with vector)', [
                    'url' => $url,
                    'rank' => $rank,
                    'existing_vector_rank' => $documents[$url]['vector_rank'] ?? null,
                ]);
            }

            $scores[$url] += $rrfScore;

            // Store original text score for debugging
            $documents[$url]['text_score'] = $result['score'] ?? null;
            $documents[$url]['text_rank'] = $rank + 1;
        }

        // Sort by RRF score (descending)
        arsort($scores);

        // Build final results array
        $results = [];
        foreach ($scores as $url => $score) {
            $doc = $documents[$url];
            $doc['rrf_score'] = round($score, 6);
            $doc['url'] = $url;

            // Determine which method(s) found this result
            $methods = [];
            if (isset($doc['vector_rank'])) {
                $methods[] = 'vector';
            }
            if (isset($doc['text_rank'])) {
                $methods[] = 'text';
            }
            $doc['found_by'] = implode('+', $methods);

            $results[] = $doc;
        }

        Log::channel('webscraper')->debug('HybridSearch: RRF completed', [
            'total_documents' => count($results),
            'vector_only' => count(array_filter($results, fn($r) => $r['found_by'] === 'vector')),
            'text_only' => count(array_filter($results, fn($r) => $r['found_by'] === 'text')),
            'both' => count(array_filter($results, fn($r) => $r['found_by'] === 'vector+text')),
        ]);

        return $results;
    }

    /**
     * Extract URL from search result
     *
     * @param array|object $result Search result (array or MongoDB BSONDocument)
     * @return string|null URL
     */
    protected function extractUrl(array|object $result): ?string
    {
        // Convert BSONDocument to array if needed
        if (is_object($result)) {
            $result = (array) $result;
        }

        // Try different URL field names
        return $result['url']
            ?? $result['page_url']
            ?? $result['source']
            ?? null;
    }

    /**
     * Normalize document structure from different search sources
     *
     * @param array|object $result Original result (array or MongoDB BSONDocument)
     * @param string $source Source type ('vector' or 'text')
     * @return array Normalized document
     */
    protected function normalizeDocument(array|object $result, string $source): array
    {
        // Convert BSONDocument to array if needed
        if (is_object($result)) {
            $result = (array) $result;
        }

        if ($source === 'vector') {
            // Vector search returns chunks with page info
            return [
                'title' => $result['title'] ?? 'No title',
                'content' => $result['content'] ?? '',
                'description' => $result['description'] ?? '',
                'domain' => $result['domain'] ?? null,
                'url' => $this->extractUrl($result),
                'chunk_index' => $result['chunk_index'] ?? null,
                'word_count' => $result['word_count'] ?? null,
            ];
        } else {
            // Text search returns pages/chunks
            return [
                'title' => $result['title'] ?? 'No title',
                'content' => $result['content'] ?? '',
                'description' => $result['description'] ?? '',
                'domain' => $result['domain'] ?? null,
                'url' => $this->extractUrl($result),
                'chunk_index' => $result['chunk_index'] ?? null,
                'word_count' => $result['word_count'] ?? null,
            ];
        }
    }

    /**
     * Set RRF constant
     *
     * @param int $k RRF constant
     * @return self
     */
    public function setK(int $k): self
    {
        $this->k = $k;
        return $this;
    }

    /**
     * Get RRF constant
     *
     * @return int
     */
    public function getK(): int
    {
        return $this->k;
    }
}