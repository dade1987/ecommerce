<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\SitemapGenerator;
use Spatie\Sitemap\Tags\Url;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // modify this to your own needs
        SitemapGenerator::create(config('app.url'))
            ->hasCrawled(function (Url $url) {
                if ($url->path() === '/') {
                    $url->setPriority(1.0);
                } else {
                    $url->setPriority(0.8);
                }

                $url->setLastModificationDate(Carbon::now());
                
                if (str_contains($url->path(), '/blog')) {
                    $url->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY);
                } else {
                    $url->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
                }

                return $url;
            })
            ->writeToFile(public_path('sitemap.xml'));
    }
}
