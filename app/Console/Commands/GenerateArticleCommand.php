<?php

namespace App\Console\Commands;

use App\Jobs\GenerateArticleJob;
use App\Models\Menu;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use function Safe\file;
use function Safe\shuffle;

class GenerateArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-article {--test : Generate only one article for testing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new article from a keyword and an external AI Service.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $keywordsPath = base_path('keywords.txt');

        if (!File::exists($keywordsPath)) {
            $this->error('keywords.txt file not found!');
            return 1;
        }

        $keywords = file($keywordsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($keywords)) {
            $this->info('No more keywords to process.');
            return 0;
        }

        if ($this->option('test')) {
            $keywords = [array_shift($keywords)];
            $this->info('Test mode: generating only one article.');
        } else {
            shuffle($keywords);
            //$keywords = array_slice($keywords, 0, 3);
        }

        $this->info('Dispatching jobs for ' . count($keywords) . ' keywords.');

        $locations = ['Milano'];

        $internalLinksList = '';
        $navbar = Menu::with('items')->where('name', 'Navbar')->first();
        if ($navbar && $navbar->relationLoaded('items')) {
            /** @var \Illuminate\Database\Eloquent\Collection $items */
            $items = $navbar->getRelation('items');
            $internalLinksList = $items->map(function ($item) {
                return "- {$item->name}: {$item->href}";
            })->implode("\n");
        }

        foreach ($keywords as $keyword) {
            foreach ($locations as $location) {
                GenerateArticleJob::dispatch($keyword, $location, $internalLinksList);
                $this->info("Job dispatched for keyword: \"{$keyword} a {$location}\"");
            }
        }
        $this->info('All jobs have been dispatched.');
        return 0;
    }
}
