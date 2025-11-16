<?php

namespace Modules\WebScraper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateChunksDomain extends Command
{
    protected $signature = 'rag:update-chunks-domain';
    protected $description = 'Update existing chunks with domain field for filtering';

    public function handle()
    {
        $this->info('🔧 Updating chunks with domain field...');
        $this->newLine();

        // Get all pages with their domains
        $pages = DB::connection('mongodb')
            ->getCollection('webscraper_pages')
            ->find([], ['projection' => ['_id' => 1, 'domain' => 1]])
            ->toArray();

        $this->info("Found " . count($pages) . " pages");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($pages));
        $bar->start();

        $totalUpdated = 0;
        foreach ($pages as $page) {
            $pageId = (string)$page['_id']; // Convert ObjectId to string
            $domain = $page['domain'] ?? null;

            if (!$domain) {
                $this->warn("Skipping page {$pageId} (no domain)");
                $bar->advance();
                continue;
            }

            // Update all chunks for this page
            $result = DB::connection('mongodb')
                ->getCollection('webscraper_chunks')
                ->updateMany(
                    ['page_id' => $pageId], // Now matching string to string
                    ['$set' => ['domain' => $domain]]
                );

            $totalUpdated += $result->getModifiedCount();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✨ Total chunks updated: {$totalUpdated}");

        return 0;
    }
}