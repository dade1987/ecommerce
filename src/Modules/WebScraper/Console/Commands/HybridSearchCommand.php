<?php

namespace Modules\WebScraper\Console\Commands;

use Illuminate\Console\Command;
use Modules\WebScraper\Services\HybridSearchService;

class HybridSearchCommand extends Command
{
    protected $signature = 'rag:hybrid-search
                            {query : Search query}
                            {--domain= : Filter by domain}
                            {--topK=10 : Number of results}';

    protected $description = 'Test hybrid search (Vector + Text Search with RRF)';

    public function handle()
    {
        $query = $this->argument('query');
        $domain = $this->option('domain');
        $topK = (int)$this->option('topK');

        $this->info('🔍 Hybrid Search (RRF Algorithm)');
        $this->newLine();
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("Query: {$query}");
        if ($domain) {
            $this->info("Domain: {$domain}");
        }
        $this->info("Top-K: {$topK}");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        try {
            $hybridSearch = app(HybridSearchService::class);

            $this->info('⏳ Searching...');
            $this->newLine();

            $results = $hybridSearch->search(
                query: $query,
                domain: $domain,
                options: ['topK' => $topK]
            );

            // Display stats
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📊 STATISTICS');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Vector Results', $results['stats']['vector_results']],
                    ['Text Results', $results['stats']['text_results']],
                    ['Merged Results', $results['stats']['merged_results']],
                    ['Final Results', $results['stats']['final_results']],
                    ['Duration (ms)', $results['stats']['duration_ms']],
                ]
            );
            $this->newLine();

            // Display results
            if (empty($results['results'])) {
                $this->warn('❌ No results found');
                return 0;
            }

            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🎯 RESULTS (sorted by RRF score)');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            foreach ($results['results'] as $index => $result) {
                $this->info(sprintf('#%d - %s', $index + 1, $result['title']));
                $this->line(sprintf('   URL: %s', $result['url']));
                $this->line(sprintf('   Domain: %s', $result['domain'] ?? 'N/A'));
                $this->line(sprintf('   RRF Score: %.6f', $result['rrf_score']));

                // Show which method found this result
                $foundBy = $result['found_by'];
                if ($foundBy === 'vector+text') {
                    $this->line(sprintf(
                        '   Found by: <fg=green>%s</> (Vector rank: %d, Text rank: %d)',
                        $foundBy,
                        $result['vector_rank'] ?? 'N/A',
                        $result['text_rank'] ?? 'N/A'
                    ));
                } elseif ($foundBy === 'vector') {
                    $this->line(sprintf(
                        '   Found by: <fg=blue>%s</> (rank: %d, score: %.4f)',
                        $foundBy,
                        $result['vector_rank'] ?? 'N/A',
                        $result['vector_score'] ?? 0
                    ));
                } else {
                    $this->line(sprintf(
                        '   Found by: <fg=yellow>%s</> (rank: %d, score: %.4f)',
                        $foundBy,
                        $result['text_rank'] ?? 'N/A',
                        $result['text_score'] ?? 0
                    ));
                }

                // Content preview
                if (isset($result['content'])) {
                    $preview = substr($result['content'], 0, 150);
                    $this->line(sprintf('   Preview: %s...', $preview));
                }

                $this->newLine();
            }

            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info(sprintf('✨ Found %d results in %.2f ms', count($results['results']), $results['stats']['duration_ms']));

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->newLine();
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}