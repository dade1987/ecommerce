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
        public int $articleId,
        public string $targetHref,
        public string $keyword,
    ) {
    }

    public function handle(SeoArticleGenerator $generator): void
    {
        /** @var Article|null $article */
        $article = Article::query()->find($this->articleId);
        if (! $article) {
            Log::warning('GenerateSeoArticleFromKeywordJob: articolo non trovato', [
                'article_id' => $this->articleId,
                'keyword' => $this->keyword,
            ]);

            return;
        }

        try {
            $generator->generateInto($article, $this->targetHref, $this->keyword);

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
                'article_id' => $this->articleId,
                'keyword' => $this->keyword,
                'error' => $e->getMessage(),
            ]);

            // Lascia traccia visibile nel record
            $article->update([
                'summary' => 'Errore generazione: '.$e->getMessage(),
                'content' => "Errore durante la generazione automatica.\n\nKeyword: {$this->keyword}\n\nDettagli: {$e->getMessage()}",
            ]);
        }
    }
}
