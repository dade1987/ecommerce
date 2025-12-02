<?php

namespace Modules\WebScraper\Console\Commands;

use Illuminate\Console\Command;
use Modules\WebScraper\Services\ClientSiteQaService;
use Modules\WebScraper\Services\SiteIndexerService;
use Illuminate\Support\Facades\Log;
use App\Models\WebsiteSearch;

class RagSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:search
                            {url : The website URL to search}
                            {query : Your question}
                            {--top-k=5 : Number of similar chunks to retrieve}
                            {--min-similarity=0.7 : Minimum similarity score (0-1)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search indexed content using RAG (Retrieval-Augmented Generation)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $query = $this->argument('query');
        $topK = (int) $this->option('top-k');
        $minSimilarity = (float) $this->option('min-similarity');

        $domain = parse_url($url, PHP_URL_HOST);

        $this->info("🔍 RAG Search");
        $this->info("Domain: {$domain}");
        $this->info("Query: {$query}");
        $this->newLine();

        try {
            // Check indexing statistics first
            $indexer = app(SiteIndexerService::class);
            $stats = $indexer->getStats();

            if ($stats['indexed_pages'] === 0) {
                $this->warn('⚠️  No pages indexed yet!');
                $this->info('💡 Run: php artisan rag:index-site "{url}" first');
                return 1;
            }

            $this->info("📊 Indexed: {$stats['indexed_pages']} pages, {$stats['total_chunks']} chunks");
            $this->newLine();

            // Perform RAG search
            $this->info('🤖 Searching with RAG...');
            $qaService = app(ClientSiteQaService::class);
            $qaService->setTopK($topK);
            $qaService->setMinSimilarity($minSimilarity);

            $result = $qaService->answerQuestion($query, $domain);

            // Display results
            $this->newLine();

            if ($result['chunks_found'] === 0) {
                $this->warn('⚠️  No relevant chunks found');
                $this->info('💡 Try:');
                $this->info('  - Lowering --min-similarity (current: ' . $minSimilarity . ')');
                $this->info('  - Increasing --top-k (current: ' . $topK . ')');
                $this->info('  - Indexing more pages');
                return 0;
            }

            $this->info("✅ Found {$result['chunks_found']} relevant chunks");
            $this->newLine();

            // Answer
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📝 ANSWER:');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();
            $this->line($result['answer']);
            $this->newLine();

            // Sources
            if (!empty($result['sources'])) {
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->info('🔗 SOURCES:');
                $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->newLine();

                $sourceData = [];
                foreach ($result['sources'] as $source) {
                    $sourceData[] = [
                        $source['title'],
                        $source['url'],
                        number_format($source['score'], 4),
                    ];
                }

                $this->table(
                    ['Title', 'URL', 'Score'],
                    $sourceData
                );
            }

            $this->newLine();
            $this->info('✨ Search completed!');
            $this->info("⚡ Method: {$result['method']}");

            // Salva la ricerca nel database WebsiteSearch
            try {
                \App\Models\WebsiteSearch::create([
                    'website' => $url,
                    'query' => $query,
                    'team_id' => null, // Comando CLI, nessun team associato
                    'locale' => 'it',
                    'response' => $result['answer'] ?? null,
                    'content_length' => strlen($result['answer'] ?? ''),
                    'from_cache' => true, // RAG search usa contenuto indicizzato (cache)
                ]);
            } catch (\Throwable $e) {
                $this->warn('Failed to save WebsiteSearch: ' . $e->getMessage());
                Log::error('RagSearchCommand: Failed to save WebsiteSearch', [
                    'error' => $e->getMessage(),
                    'url' => $url,
                    'query' => $query,
                ]);
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Search failed: ' . $e->getMessage());
            Log::error('RagSearchCommand: Fatal error', [
                'url' => $url,
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}