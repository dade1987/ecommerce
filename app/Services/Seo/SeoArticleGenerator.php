<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\MenuItem;
use App\Models\Tag;
use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebScraper\Services\WebScraperService;
use OpenAI;
use function Safe\json_decode;
use function Safe\json_encode;

class SeoArticleGenerator
{
    public function __construct(
        protected MenuItemUrlResolver $urlResolver,
        protected WebScraperService $scraper,
    ) {
    }

    public function generate(MenuItem $menuItem, string $keyword): Article
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

        $targetUrl = $this->urlResolver->resolve($menuItem);
        $targetHref = is_string($menuItem->href) ? $menuItem->href : '';

        $scraped = $this->scraper->scrape($targetUrl);
        if (isset($scraped['error'])) {
            throw new \RuntimeException('Errore scraping URL: '.$scraped['error']);
        }

        $pageTitle = (string) ($scraped['metadata']['title'] ?? '');
        $pageDesc = (string) ($scraped['metadata']['description'] ?? '');
        $pageHeadings = [];
        if (isset($scraped['content']) && is_array($scraped['content'])) {
            $headings = $scraped['content']['headings'] ?? [];
            if (is_array($headings)) {
                $pageHeadings = $headings;
            }
        }
        $main = (string) ($scraped['content']['main'] ?? '');
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
SYS,
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'task' => 'Genera un articolo SEO da indicizzare su una keyword, con CTA verso una pagina specifica del sito.',
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

        // Tag di default (riuso pattern esistente del job)
        try {
            $tag = Tag::firstOrCreate(
                ['slug' => 'articolo-smart'],
                ['name' => 'Articolo Smart']
            );
            $article->tags()->syncWithoutDetaching([$tag->id]);
        } catch (\Throwable $e) {
            // Non bloccare la creazione articolo se il tagging fallisce
        }

        return $article;
    }
}
