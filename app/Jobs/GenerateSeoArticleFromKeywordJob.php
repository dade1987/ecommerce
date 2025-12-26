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
use Illuminate\Support\Str;

class GenerateSeoArticleFromKeywordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $targetHref,
        public string $keyword,
        public string $language = 'it',
    ) {
    }

    public function handle(SeoArticleGenerator $generator): void
    {
        try {
            // Crea SEMPRE un record per keyword (così ne vedi uno per job anche se fallisce)
            $kw = trim($this->keyword);
            $placeholderTitle = $kw !== '' ? "In generazione: {$kw}" : 'In generazione';
            $placeholderSlug = Str::slug('in-generazione-'.$kw.'-'.Str::random(6));

            $article = Article::create([
                'title' => $placeholderTitle,
                'slug' => $placeholderSlug,
                'summary' => 'Generazione in corso…',
                'content' => "Generazione in coda.\n\nKeyword: {$this->keyword}\nLingua: {$this->language}\nTarget: {$this->targetHref}",
            ]);

            $generator->generateInto($article, $this->targetHref, $this->keyword, $this->language);

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

            // Se esiste un placeholder creato prima dell'errore, renderlo “diagnosticabile”
            try {
                if (isset($article) && $article instanceof Article) {
                    $article->update([
                        'summary' => 'Errore generazione: '.$e->getMessage(),
                        'content' => "Errore durante la generazione automatica.\n\nKeyword: {$this->keyword}\n\nDettagli: {$e->getMessage()}",
                    ]);
                }
            } catch (\Throwable $inner) {
                // non bloccare
            }
        }
    }
}
