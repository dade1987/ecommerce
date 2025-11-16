<?php

namespace Modules\WebScraper\Console\Commands;

use Illuminate\Console\Command;
use Modules\WebScraper\Services\WebScraperService;
use Modules\WebScraper\Services\SiteIndexerService;
use Modules\WebScraper\Models\WebscraperPage;
use Illuminate\Support\Facades\Log;

class IndexSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:index-site
                            {url : The website URL to index}
                            {--max-pages=50 : Maximum number of pages to index}
                            {--ttl=30 : Time-to-live in days (0 = never expires)}
                            {--force : Force re-indexing even if already indexed}
                            {--use-sitemap : Use sitemap.xml instead of crawling}
                            {--crawl : Force crawling instead of using sitemap}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index a website for RAG (Retrieval-Augmented Generation) system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $maxPages = (int) $this->option('max-pages');
        $ttl = (int) $this->option('ttl');
        $force = $this->option('force');

        $this->info("🚀 Starting site indexing for: {$url}");
        $this->info("📊 Max pages: {$maxPages}");
        $this->info("⏰ TTL: " . ($ttl === 0 ? 'Never expires' : "{$ttl} days"));
        $this->newLine();

        try {
            // Check if already indexed
            $domain = parse_url($url, PHP_URL_HOST);
            $existingPages = WebscraperPage::where('domain', $domain)
                ->where('status', 'indexed')
                ->count();

            if ($existingPages > 0 && !$force) {
                $this->warn("⚠️  Site already has {$existingPages} indexed pages.");
                if (!$this->confirm('Do you want to continue and add more pages?')) {
                    $this->info('Indexing cancelled.');
                    return 0;
                }
            }

            // Step 1: Get all URLs (sitemap or crawling)
            $scraper = app(WebScraperService::class);
            $useSitemap = $this->option('use-sitemap');
            $forceCrawl = $this->option('crawl');

            // Auto-detect: try sitemap first unless crawl is forced
            if (!$forceCrawl && !$useSitemap) {
                $this->info('🔍 Checking for sitemap.xml...');
                $sitemapService = app(\Modules\WebScraper\Services\SitemapService::class);
                $sitemapUrls = $sitemapService->parseSitemap($url);

                if (!empty($sitemapUrls)) {
                    $this->info("✅ Found sitemap with " . count($sitemapUrls) . " URLs");
                    $useSitemap = true;
                } else {
                    $this->warn('⚠️  No sitemap found, falling back to crawling');
                    $forceCrawl = true;
                }
            }

            $urls = [];

            if ($useSitemap && !$forceCrawl) {
                $this->info('📡 Step 1/3: Fetching URLs from sitemap...');
                $sitemapService = app(\Modules\WebScraper\Services\SitemapService::class);
                $urls = $sitemapService->parseSitemap($url);

                // Limit to max pages
                if (count($urls) > $maxPages) {
                    $this->warn("⚠️  Sitemap has " . count($urls) . " URLs, limiting to {$maxPages}");
                    $urls = array_slice($urls, 0, $maxPages);
                }

                $this->info("✅ Found " . count($urls) . " URLs from sitemap");
            } else {
                $this->info('📡 Step 1/3: Crawling site to discover pages...');

                $bar = $this->output->createProgressBar($maxPages);
                $bar->setFormat('verbose');

                $crawledPages = $scraper->crawlSite($url, $maxPages);
                $bar->finish();
                $this->newLine(2);

                // Extract URLs from crawled pages
                $urls = array_column($crawledPages, 'url');
                $this->info("✅ Found " . count($urls) . " pages via crawling");
            }

            $this->newLine();

            // Step 2: Index each page (scrape + chunk + embed)
            $this->info('🔍 Step 2/3: Indexing pages (chunking + embedding)...');
            $indexer = app(SiteIndexerService::class);

            $bar = $this->output->createProgressBar(count($urls));
            $bar->setFormat('verbose');

            $indexed = 0;
            $failed = 0;
            $skipped = 0;

            foreach ($urls as $pageUrl) {
                try {
                    // Check if already indexed and not expired
                    $urlHash = WebscraperPage::generateUrlHash($pageUrl);
                    $existing = WebscraperPage::where('url_hash', $urlHash)->first();

                    if ($existing && !$existing->isExpired() && !$force) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Index the page
                    $ttlDays = $ttl === 0 ? null : $ttl;
                    $page = $indexer->indexUrl($pageUrl, $ttlDays);

                    $indexed++;
                    $bar->advance();

                } catch (\Exception $e) {
                    $failed++;
                    Log::error('IndexSiteCommand: Failed to index page', [
                        'url' => $pageUrl,
                        'error' => $e->getMessage(),
                    ]);
                    $bar->advance();
                    continue;
                }
            }

            $bar->finish();
            $this->newLine(2);

            // Step 3: Show statistics
            $this->info('📈 Step 3/3: Indexing statistics');
            $this->newLine();

            $stats = $indexer->getStats();

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Pages in DB', $stats['total_pages']],
                    ['Indexed Pages', $stats['indexed_pages']],
                    ['Failed Pages', $stats['failed_pages']],
                    ['Processing Pages', $stats['processing_pages']],
                    ['Total Chunks', $stats['total_chunks']],
                    ['Avg Chunks/Page', $stats['avg_chunks_per_page']],
                    ['---', '---'],
                    ['This Session - Indexed', $indexed],
                    ['This Session - Skipped', $skipped],
                    ['This Session - Failed', $failed],
                ]
            );

            $this->newLine();
            $this->info('✨ Site indexing completed!');
            $this->info('💡 You can now use RAG search with: php artisan rag:search "{url}" "your question"');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Indexing failed: ' . $e->getMessage());
            Log::error('IndexSiteCommand: Fatal error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}