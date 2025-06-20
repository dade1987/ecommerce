<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class DeleteArticlesWithoutImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-articles-without-image {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all articles that do not have a featured image.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $articlesToDelete = Article::whereNull('featured_image_id')->get();

        $count = $articlesToDelete->count();

        if ($count === 0) {
            $this->info('No articles without a featured image found.');
            return 0;
        }

        $this->warn("Found {$count} articles without a featured image.");

        if (!$this->option('force')) {
            if (!$this->confirm('Do you really want to delete these articles?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        foreach ($articlesToDelete as $article) {
            $this->info("Deleting article: \"{$article->title}\" (ID: {$article->id})");
            $article->delete();
        }

        $this->info("Successfully deleted {$count} articles.");
        return 0;
    }
}
