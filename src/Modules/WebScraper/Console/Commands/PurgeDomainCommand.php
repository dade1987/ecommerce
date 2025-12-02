<?php

namespace Modules\WebScraper\Console\Commands;

use Illuminate\Console\Command;
use Modules\WebScraper\Models\WebscraperPage;
use Modules\WebScraper\Models\WebscraperChunk;

class PurgeDomainCommand extends Command
{
    protected $signature = 'rag:purge-domain
                            {domain : Domain to purge (e.g., example.com)}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Purge all indexed data for a specific domain (pages + chunks from MongoDB Atlas)';

    public function handle()
    {
        $domain = $this->argument('domain');

        // Normalize domain (remove protocol if present)
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        $this->info("Domain to purge: {$domain}");
        $this->newLine();

        // Count existing data
        $pageCount = WebscraperPage::where('domain', $domain)->count();
        $chunkCount = WebscraperChunk::where('domain', $domain)->count();

        if ($pageCount === 0 && $chunkCount === 0) {
            $this->warn("No data found for domain: {$domain}");
            return 0;
        }

        $this->line("Found:");
        $this->line("  - Pages: {$pageCount}");
        $this->line("  - Chunks: {$chunkCount}");
        $this->newLine();

        // Confirm deletion
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete all data for {$domain}?")) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Purging data...');
        $this->newLine();

        // Delete chunks first (they reference pages)
        $deletedChunks = WebscraperChunk::where('domain', $domain)->delete();
        $this->line("  Deleted {$deletedChunks} chunks");

        // Delete pages
        $deletedPages = WebscraperPage::where('domain', $domain)->delete();
        $this->line("  Deleted {$deletedPages} pages");

        $this->newLine();
        $this->info("Successfully purged all data for: {$domain}");
        $this->newLine();
        $this->line("To re-index this domain, run:");
        $this->line("  php artisan rag:index-site \"https://{$domain}\"");

        return 0;
    }
}