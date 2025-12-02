<?php

namespace Modules\WebScraper\Console\Commands;

use Illuminate\Console\Command;
use Modules\WebScraper\Models\ScrapedPage;

class ClearExpiredCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'webscraper:clear-expired
                            {--all : Clear all cache, not just expired}
                            {--stats : Show cache statistics}';

    /**
     * The console command description.
     */
    protected $description = 'Clear expired WebScraper cache entries from SQLite database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Show stats if requested
        if ($this->option('stats')) {
            $this->showStats();
            return 0;
        }

        // Clear all cache if requested
        if ($this->option('all')) {
            $deleted = ScrapedPage::clearAll();
            $this->info("✓ Cleared all cache ({$deleted} entries deleted)");
            return 0;
        }

        // Clear only expired cache
        $deleted = ScrapedPage::clearExpired();

        if ($deleted > 0) {
            $this->info("✓ Cleared {$deleted} expired cache entries");
        } else {
            $this->info("✓ No expired cache entries found");
        }

        // Show current stats
        $this->showStats();

        return 0;
    }

    /**
     * Show cache statistics
     */
    protected function showStats(): void
    {
        $stats = ScrapedPage::getStats();

        $this->newLine();
        $this->info('WebScraper Cache Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', $stats['total_entries']],
                ['Valid Entries', $stats['valid_entries']],
                ['Expired Entries', $stats['expired_entries']],
                ['Database Size', $this->formatBytes($stats['database_size'])],
            ]
        );
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log(1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}