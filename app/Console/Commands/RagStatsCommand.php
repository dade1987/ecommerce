<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\WebScraper\Services\SiteIndexerService;
use Modules\WebScraper\Models\WebscraperPage;

class RagStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:stats {--domain= : Filter by specific domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show RAG system statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->option('domain');

        $this->info('📊 RAG System Statistics');
        $this->newLine();

        try {
            $indexer = app(SiteIndexerService::class);
            $stats = $indexer->getStats();

            // Global statistics
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🌍 GLOBAL STATS');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Pages', $stats['total_pages']],
                    ['Indexed Pages', $stats['indexed_pages']],
                    ['Failed Pages', $stats['failed_pages']],
                    ['Processing Pages', $stats['processing_pages']],
                    ['Total Chunks', $stats['total_chunks']],
                    ['Avg Chunks/Page', $stats['avg_chunks_per_page']],
                ]
            );

            $this->newLine();

            // Domain breakdown
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('🌐 BY DOMAIN');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            $query = WebscraperPage::query();

            if ($domain) {
                $query->where('domain', $domain);
            }

            // MongoDB aggregation instead of selectRaw
            $allPages = $query->get(['domain', 'status', 'chunk_count']);

            if ($allPages->isEmpty()) {
                $this->warn('No pages indexed yet.');
                $this->newLine();
                $this->info('💡 Run: php artisan rag:index-site "https://example.com"');
            } else {
                // Group manually
                $grouped = $allPages->groupBy('domain');

                foreach ($grouped as $domainName => $pages) {
                    $this->info("Domain: {$domainName}");

                    // Group by status
                    $byStatus = $pages->groupBy('status');
                    $tableData = [];

                    foreach ($byStatus as $statusName => $statusPages) {
                        $tableData[] = [
                            $statusName,
                            $statusPages->count(),
                            $statusPages->sum('chunk_count'),
                        ];
                    }

                    $this->table(
                        ['Status', 'Pages', 'Chunks'],
                        $tableData
                    );
                    $this->newLine();
                }
            }

            // Recent indexed pages
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('📅 RECENT INDEXED PAGES (last 10)');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->newLine();

            $recentQuery = WebscraperPage::query()
                ->where('status', 'indexed')
                ->orderBy('indexed_at', 'desc')
                ->limit(10);

            if ($domain) {
                $recentQuery->where('domain', $domain);
            }

            $recentPages = $recentQuery->get();

            if ($recentPages->isEmpty()) {
                $this->warn('No indexed pages yet.');
            } else {
                $recentData = [];
                foreach ($recentPages as $page) {
                    $recentData[] = [
                        substr($page->title ?? 'No title', 0, 40),
                        $page->domain,
                        $page->chunk_count,
                        $page->word_count,
                        $page->indexed_at?->diffForHumans() ?? 'N/A',
                    ];
                }

                $this->table(
                    ['Title', 'Domain', 'Chunks', 'Words', 'Indexed'],
                    $recentData
                );
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to get statistics: ' . $e->getMessage());
            return 1;
        }
    }
}