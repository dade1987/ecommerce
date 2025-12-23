<?php

namespace App\Services\Seo;

use App\Models\Article;
use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenAI;
use function Safe\json_decode;
use function Safe\json_encode;

class SeoArticleGenerator
{
    public function __construct(
        protected MenuItemUrlResolver $urlResolver,
        protected PageSourceWithScriptsFetcher $pageFetcher,
    ) {
    }

    public function generate(string $targetHref, string $keyword): Article
    {
        $apiKeyConfig = config('openapi.key');
        $apiKey = is_string($apiKeyConfig) ? $apiKeyConfig : '';
        if (trim($apiKey) === '') {
            throw new \RuntimeException('OpenAI API key non configurata (config/openapi.key).');
        }

        $keyword = trim($keyword);
        if ($keyword === '') {
            throw new \InvalidArgumentException('Keyword vuota.');
        }

        $targetHref = trim($targetHref);
        if ($targetHref === '') {
            throw new \InvalidArgumentException('URL target vuoto.');
        }

        $targetUrl = $this->urlResolver->resolveHref($targetHref);

        $scraped = $this->pageFetcher->fetch($targetHref);
        if (isset($scraped['error'])) {
            throw new \RuntimeException('Errore scraping (include JS sources): '.$scraped['error']);
        }

        $metadata = $scraped['metadata'] ?? [];
        $metadata = is_array($metadata) ? $metadata : [];

        $pageTitleRaw = $metadata['title'] ?? '';
        $pageDescRaw = $metadata['description'] ?? '';
        $pageTitle = is_string($pageTitleRaw) ? $pageTitleRaw : '';
        $pageDesc = is_string($pageDescRaw) ? $pageDescRaw : '';

        $pageHeadings = [];
        if (isset($scraped['content']) && is_array($scraped['content'])) {
            $headings = $scraped['content']['headings'] ?? [];
            if (is_array($headings)) {
                $pageHeadings = $headings;
            }
        }
        $mainRaw = (isset($scraped['content']) && is_array($scraped['content'])) ? ($scraped['content']['main'] ?? '') : '';
        $main = is_string($mainRaw) ? $mainRaw : '';
        $main = mb_substr($main, 0, 8000);

        $client = OpenAI::client($apiKey);

        $response = $client->chat()->create([
            'model' => 'gpt-4o',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => <<<'SYS'
Sei un Marketing Specialist esperto di landing page e un SEO strategist.
Scrivi contenuti orientati alla conversione, chiari, credibili, con CTA efficace e struttura ottimizzata per Google.
Scrivi in italiano. Non dire mai che sei un'IA.
IMPORTANTE: devi rispondere SEMPRE e SOLO in formato JSON valido.
SYS,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'task' => 'Genera un articolo SEO da indicizzare su una keyword, con CTA verso una pagina specifica del sito.',
                        'format' => 'JSON only',
                        'keyword' => $keyword,
                        'target' => [
                            'href' => $targetHref,
                            'url' => $targetUrl,
                        ],
                        'page_context' => [
                            'title' => $pageTitle,
                            'description' => $pageDesc,
                            'headings' => $pageHeadings,
                            'main_content_excerpt' => $main,
                        ],
                        'output_contract' => [
                            'title' => 'string (SEO, include keyword)',
                            'slug' => 'string (kebab-case, senza slash)',
                            'summary' => 'string (max 160-180 caratteri)',
                            'content_markdown' => 'string (usa ##, ###, liste, tabelle se utili, e almeno 1 link verso target.href)',
                            'meta_title' => 'string (max 60-65)',
                            'meta_description' => 'string (max 155-160)',
                            'meta_keywords' => 'string (comma-separated, 5-12 keyword)',
                            'og_title' => 'string',
                            'og_description' => 'string',
                            'twitter_title' => 'string',
                            'twitter_description' => 'string',
                            'image_prompt' => 'string (prompt per DALL·E 3, stile coerente col contenuto, niente testo nell’immagine)',
                        ],
                        'constraints' => [
                            'Evita keyword stuffing',
                            'Inserisci una sezione FAQ (3-5 domande) se pertinente',
                            'Inserisci una CTA finale che invita a visitare la pagina target',
                            'Inserisci link in markdown (es: [Testo](/percorso))',
                            'Se la keyword NON è una corrispondenza perfetta con la pagina target, adatta l’articolo in modo credibile: spiega come la soluzione della pagina target può coprire quel bisogno (es. “traduttore arabo italiano” → interprete virtuale con supporto multilingua) senza fare promesse false.',
                        ],
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ],
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ]);

        $raw = (string) ($response->choices[0]->message->content ?? '');
        try {
            $data = json_decode($raw, true);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Risposta AI non parsabile in JSON.');
        }
        if (! is_array($data)) {
            throw new \RuntimeException('Risposta AI non parsabile in JSON.');
        }

        $title = trim((string) ($data['title'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        $summary = trim((string) ($data['summary'] ?? ''));
        $content = (string) ($data['content_markdown'] ?? '');

        if ($title === '' || $content === '') {
            throw new \RuntimeException('Risposta AI incompleta (title/content).');
        }

        if ($slug === '') {
            $slug = Str::slug($title);
        } else {
            $slug = Str::slug($slug);
        }

        // Immagine con DALL·E 3 (ultima versione richiesta)
        $imagePrompt = trim((string) ($data['image_prompt'] ?? ''));
        if ($imagePrompt === '') {
            $imagePrompt = "Immagine moderna e professionale per un articolo SEO su: {$keyword}. Stile pulito, business, minimal, nessun testo.";
        }

        $imageResponse = $client->images()->create([
            'model' => 'dall-e-3',
            'prompt' => $imagePrompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ]);

        $imageUrl = $imageResponse->data[0]->url ?? null;
        if (! is_string($imageUrl) || trim($imageUrl) === '') {
            throw new \RuntimeException('Impossibile ottenere URL immagine da OpenAI.');
        }

        $imageBinary = Http::timeout(30)->get($imageUrl)->body();
        if (! is_string($imageBinary) || $imageBinary === '') {
            throw new \RuntimeException('Impossibile scaricare immagine generata.');
        }

        $imageName = $slug.'-'.Str::random(6).'.png';
        $imagePath = 'images/'.$imageName;
        Storage::disk('public')->put($imagePath, $imageBinary);

        $media = Media::create([
            'name' => $title,
            'path' => $imagePath,
            'disk' => 'public',
            'type' => 'image/png',
            'size' => strlen($imageBinary),
        ]);

        $article = Article::create([
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'summary' => $summary,
            'featured_image_id' => $media->id,
            'meta_title' => (string) ($data['meta_title'] ?? null),
            'meta_description' => (string) ($data['meta_description'] ?? null),
            'meta_keywords' => (string) ($data['meta_keywords'] ?? null),
            'og_title' => (string) ($data['og_title'] ?? null),
            'og_description' => (string) ($data['og_description'] ?? null),
            'twitter_title' => (string) ($data['twitter_title'] ?? null),
            'twitter_description' => (string) ($data['twitter_description'] ?? null),
        ]);

        return $article;
    }

    public function generateInto(Article $article, string $targetHref, string $keyword): void
    {
        $generated = $this->generate($targetHref, $keyword);

        // Copia i campi sul record placeholder (mantiene id stabile)
        $generatedSlugAttr = $generated->getAttribute('slug');
        $generatedSlug = is_string($generatedSlugAttr) ? $generatedSlugAttr : '';

        $article->update([
            'title' => $generated->title,
            'slug' => $this->makeUniqueSlug($generatedSlug, (int) $article->id),
            'content' => $generated->content,
            'summary' => $generated->summary,
            'featured_image_id' => $generated->featured_image_id,
            'meta_title' => $generated->meta_title,
            'meta_description' => $generated->meta_description,
            'meta_keywords' => $generated->meta_keywords,
            'og_title' => $generated->og_title,
            'og_description' => $generated->og_description,
            'twitter_title' => $generated->twitter_title,
            'twitter_description' => $generated->twitter_description,
        ]);

        // Elimina il record temporaneo creato da generate()
        try {
            $generated->delete();
        } catch (\Throwable $e) {
            // non bloccare
        }
    }

    private function makeUniqueSlug(string $slug, int $currentArticleId): string
    {
        $slug = trim($slug);
        if ($slug === '') {
            $slug = 'articolo-'.$currentArticleId;
        }

        $base = $slug;
        $i = 2;

        while (
            Article::query()
                ->where('slug', $slug)
                ->where('id', '!=', $currentArticleId)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
            if ($i > 50) {
                $slug = $base.'-'.$currentArticleId;
                break;
            }
        }

        return $slug;
    }
}
