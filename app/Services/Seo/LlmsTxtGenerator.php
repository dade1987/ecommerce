<?php

namespace App\Services\Seo;

use App\Models\Article;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use function Safe\json_decode;
use function Safe\preg_replace;

class LlmsTxtGenerator
{
    /**
     * @param Collection<int, Page> $pages
     * @param Collection<int, Article> $articles
     */
    public function generate(Collection $pages, Collection $articles, string $baseUrl): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        if ($baseUrl === '') {
            $appUrl = config('app.url');
            $appUrl = is_string($appUrl) ? trim($appUrl) : '';
            $baseUrl = rtrim($appUrl, '/');
        }

        $now = now()->toDateString();
        $appName = config('app.name');
        $project = is_string($appName) && trim($appName) !== '' ? trim($appName) : 'Sito';

        $lines = [];
        $lines[] = "# {$project}";
        $lines[] = 'guida per i Large Language Models ->';
        $lines[] = "\"aggiornamento: {$now} -> ->\"";
        $lines[] = '';

        $siteDescription = $this->guessSiteDescription($pages);
        if ($siteDescription !== '') {
            $lines[] = '> Descrizione del sito';
            $lines[] = $siteDescription;
            $lines[] = '';
        }

        $lines[] = '## Contenuti principali';
        foreach ($pages as $page) {
            $title = (string) ($page->title ?? '');
            $url = $this->pageUrl($page, $baseUrl);
            $desc = $this->pageExcerpt($page);
            $lines[] = $this->markdownLinkLine($title, $url, $desc);
        }
        $lines[] = '';

        $lines[] = '## Contenuti opzionali';
        foreach ($articles as $article) {
            $title = (string) ($article->title ?? '');
            $url = $this->articleUrl($article, $baseUrl);
            $desc = $this->articleExcerpt($article);
            $lines[] = $this->markdownLinkLine($title, $url, $desc);
        }
        $lines[] = '';

        $contact = $this->contactLine();
        if ($contact !== '') {
            $lines[] = '## Contatti';
            $lines[] = $contact;
            $lines[] = '';
        }

        return rtrim(implode("\n", $lines))."\n";
    }

    /**
     * @param Collection<int, Page> $pages
     * @param Collection<int, Article> $articles
     */
    public function writeToPublic(Collection $pages, Collection $articles, string $baseUrl, string $targetPath): string
    {
        $content = $this->generate($pages, $articles, $baseUrl);

        $dir = dirname($targetPath);
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($targetPath, $content);

        return $targetPath;
    }

    private function markdownLinkLine(string $title, string $url, string $desc): string
    {
        $title = trim($title);
        $url = trim($url);
        $desc = trim($desc);

        if ($title === '') {
            $title = $url !== '' ? $url : 'Contenuto';
        }

        if ($url === '') {
            return $desc !== '' ? "{$title}: {$desc}" : $title;
        }

        if ($desc === '') {
            return "[{$title}]({$url})";
        }

        return "[{$title}]({$url}): {$desc}";
    }

    private function pageUrl(Page $page, string $baseUrl): string
    {
        $slug = (string) ($page->slug ?? '');
        $slug = trim($slug);
        if ($slug === '' || $slug === '/') {
            return $baseUrl.'/';
        }

        return $baseUrl.'/'.ltrim($slug, '/');
    }

    private function articleUrl(Article $article, string $baseUrl): string
    {
        $slug = (string) ($article->slug ?? '');
        $slug = trim($slug);
        if ($slug === '') {
            $slug = (string) ($article->id ?? '');
        }

        $slug = ltrim($slug, '/');

        return $baseUrl.'/blog/'.$slug;
    }

    private function pageExcerpt(Page $page): string
    {
        $desc = (string) ($page->description ?? '');
        $desc = $this->plainText($desc);
        if ($desc !== '') {
            return $this->limit($desc, 160);
        }

        $blocks = $page->blocks ?? null;
        $text = $this->extractTextFromBlocks($blocks);

        return $this->limit($text, 160);
    }

    private function articleExcerpt(Article $article): string
    {
        $summary = (string) ($article->summary ?? '');
        $summary = $this->plainText($summary);
        if ($summary !== '') {
            return $this->limit($summary, 160);
        }

        $content = (string) ($article->content ?? '');
        $content = $this->plainText($content);

        return $this->limit($content, 160);
    }

    /**
     * Prova a prendere una descrizione "sensata" dal campo description delle pagine selezionate.
     *
     * @param Collection<int, Page> $pages
     */
    private function guessSiteDescription(Collection $pages): string
    {
        foreach ($pages as $p) {
            $d = $this->plainText((string) ($p->description ?? ''));
            if ($d !== '') {
                return $this->limit($d, 320);
            }
        }

        return '';
    }

    private function contactLine(): string
    {
        $mailCfg = config('mail.from.address');
        $mail = is_string($mailCfg) ? trim($mailCfg) : '';
        if ($mail === '') {
            return '';
        }

        return "[Contatto](mailto:{$mail}): Per informazioni contatta {$mail}";
    }

    private function plainText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        try {
            $value = preg_replace('/\s+/u', ' ', $value);
        } catch (\Throwable $e) {
            // fallback: non normalizzare gli spazi se PCRE fallisce
        }
        $value = is_string($value) ? trim($value) : '';

        return $value;
    }

    private function limit(string $value, int $max): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $max - 1)).'…';
    }

    /**
     * Estrae testo utile da "blocks" di Filament Fabricator (array/JSON).
     *
     * @param mixed $blocks
     */
    private function extractTextFromBlocks($blocks): string
    {
        if (is_string($blocks)) {
            try {
                $decoded = json_decode($blocks, true);
                $blocks = is_array($decoded) ? $decoded : null;
            } catch (\Throwable $e) {
                $blocks = null;
            }
        }

        if (! is_array($blocks)) {
            return '';
        }

        $strings = [];
        $this->collectStrings($blocks, $strings);

        $strings = array_values(array_filter(array_map(fn ($s) => $this->plainText((string) $s), $strings), fn ($s) => is_string($s) && trim($s) !== ''));
        $strings = array_values(array_unique($strings));

        // hard cap per evitare file enormi
        $joined = implode(' ', array_slice($strings, 0, 40));

        return trim($joined);
    }

    /**
     * @param mixed $value
     * @param array<int, string> $out
     */
    private function collectStrings($value, array &$out): void
    {
        if (is_string($value)) {
            $out[] = $value;

            return;
        }

        if (! is_array($value)) {
            return;
        }

        foreach ($value as $k => $v) {
            // ignora campi che sono quasi sempre asset/URL immagini
            if (is_string($k)) {
                $key = strtolower(trim($k));
                if (Str::endsWith($key, ['image', 'logo', 'icon', 'photo', 'picture', 'media', 'featured', 'url'])) {
                    continue;
                }
            }
            $this->collectStrings($v, $out);
        }
    }
}
