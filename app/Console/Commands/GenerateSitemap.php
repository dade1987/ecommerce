<?php

namespace App\Console\Commands;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Tags\Video;

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
        $sitemap = Sitemap::create();

        // Esempio di aggiunta della homepage con tutte le proprietà
        $sitemap->add(
            Url::create(config('app.url'))
                ->setLastModificationDate(Carbon::yesterday())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.1)
                ->addImage(config('app.url') . '/images/home.jpg', 'Immagine della home page')
                ->addVideo(
                    config('app.url') . '/videos/thumbnail.jpg',
                    'Titolo del video',
                    'Descrizione del video',
                    config('app.url') . '/videos/source.mp4',
                    config('app.url') . '/video/123',
                    [
                        'family_friendly' => Video::OPTION_YES,
                        'live' => Video::OPTION_NO
                    ]
                )
                ->addAlternate('/en', 'en_US')
        );

        // Aggiungo tutti gli articoli alla sitemap
        $articles = Article::all();
        foreach ($articles as $article) {
            $sitemap->add(
                Url::create($article->slug)
                    ->setLastModificationDate($article->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.9)
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');
    }
}
