<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\Tag;
use App\Services\Seo\SeoArticleGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSeoArticleFromKeywordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $targetHref,
        public string $keyword,
    ) {
    }

    public function handle(SeoArticleGenerator $generator): void
    {
        try {
            $article = $generator->generate($this->targetHref, $this->keyword);

            try {
                $tag = Tag::firstOrCreate(
                    ['slug' => 'articolo-smart'],
                    ['name' => 'Articolo Smart']
                );
                $article->tags()->syncWithoutDetaching([$tag->id]);
            } catch (\Throwable $e) {
                // non bloccare
            }
        } catch (\Throwable $e) {
            Log::error('GenerateSeoArticleFromKeywordJob: errore generazione', [
                'keyword' => $this->keyword,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
