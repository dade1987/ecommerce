<?php

namespace App\Services\Seo;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Modules\WebScraper\Services\HtmlParserService;
use function Safe\parse_url;
use function Safe\preg_match;

class PageSourceWithScriptsFetcher
{
    public function __construct(
        protected MenuItemUrlResolver $urlResolver,
        protected HtmlParserService $htmlParser,
    ) {
    }

    /**
     * Fetch HTML + download linked scripts (JS) and return a "scraped-like" structure.
     *
     * @return array<string, mixed>
     */
    public function fetch(string $targetHref): array
    {
        $targetHref = trim($targetHref);
        if ($targetHref === '') {
            return ['error' => 'URL target vuoto.'];
        }

        $url = $this->urlResolver->resolveHref($targetHref);

        try {
            $htmlResponse = Http::connectTimeout(5)
                ->timeout(60)
                ->withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'it-IT,it;q=0.9,en;q=0.8',
                    'User-Agent' => 'Mozilla/5.0 (compatible; EcommerceSeoBot/1.0)',
                ])
                ->get($url);
        } catch (\Throwable $e) {
            return [
                'error' => "Impossibile scaricare HTML: {$e->getMessage()}. Se l'URL è relativo, verifica APP_URL/config(app.url) o inserisci un URL assoluto raggiungibile dal server.",
            ];
        }

        if (! $htmlResponse->successful()) {
            return ['error' => 'Impossibile scaricare HTML (HTTP '.$htmlResponse->status().').'];
        }

        $html = (string) $htmlResponse->body();
        if (trim($html) === '') {
            return ['error' => 'HTML vuoto.'];
        }

        $scriptUrls = $this->extractScriptUrls($url, $html);
        $scriptPayload = $this->downloadScripts($scriptUrls);
        $inlineScripts = $this->extractInlineScripts($html);

        // Parse HTML to extract metadata + main content
        $parsed = $this->htmlParser->parse($html);
        $allText = $this->htmlParser->extractAllText($html);

        // Enrich "main" content with JS sources (so the AI sees them)
        $jsContext = $this->buildJsContextBlock($scriptPayload, $inlineScripts);
        $main = (string) ($parsed['main_content'] ?? '');
        $mainWithJs = $main;
        if ($jsContext !== '') {
            $mainWithJs .= "\n\n".$jsContext;
        }

        // Keep full text also enriched (bounded)
        $fullWithJs = $allText;
        if ($jsContext !== '') {
            $fullWithJs .= "\n\n".$jsContext;
        }

        return [
            'url' => $url,
            'scraped_at' => now()->toIso8601String(),
            'metadata' => $parsed['metadata'] ?? [],
            'content' => [
                'main' => $mainWithJs,
                'full' => $fullWithJs,
                'headings' => $parsed['headings'] ?? [],
                'structured' => $parsed['structured_content'] ?? [],
                'js' => [
                    'script_urls' => $scriptUrls,
                    'downloaded' => $scriptPayload,
                    'inline' => $inlineScripts,
                ],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractScriptUrls(string $pageUrl, string $html): array
    {
        $dom = $this->loadHtml($html);
        if (! $dom) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//script[@src]/@src');
        if ($nodes === false) {
            return [];
        }

        $urls = [];
        foreach ($nodes as $node) {
            $src = trim((string) $node->nodeValue);
            if ($src === '') {
                continue;
            }
            $urls[] = $this->resolveUrl($pageUrl, $src);
        }

        $urls = array_values(array_unique(array_filter($urls, fn ($u) => is_string($u) && trim($u) !== '')));

        // hard cap
        return array_slice($urls, 0, 30);
    }

    /**
     * @return array<int, string>
     */
    private function extractInlineScripts(string $html): array
    {
        $dom = $this->loadHtml($html);
        if (! $dom) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//script[not(@src)]');
        if ($nodes === false) {
            return [];
        }

        $chunks = [];
        foreach ($nodes as $node) {
            $text = trim((string) $node->textContent);
            if ($text === '') {
                continue;
            }
            $chunks[] = $text;
        }

        return array_slice($chunks, 0, 20);
    }

    /**
     * @param array<int, string> $scriptUrls
     * @return array<int, array{url:string, status:int|null, bytes:int, excerpt:string}>
     */
    private function downloadScripts(array $scriptUrls): array
    {
        $out = [];
        $maxScripts = 20;
        $maxBytesPerScript = 1024 * 1024; // 1MB
        $maxExcerpt = 20000; // chars

        foreach (array_slice($scriptUrls, 0, $maxScripts) as $u) {
            try {
                $resp = Http::connectTimeout(5)->timeout(30)->withHeaders([
                    'Accept' => '*/*',
                    'User-Agent' => 'Mozilla/5.0 (compatible; EcommerceSeoBot/1.0)',
                ])->get($u);

                $body = (string) $resp->body();
                if (strlen($body) > $maxBytesPerScript) {
                    $body = substr($body, 0, $maxBytesPerScript);
                }
                $excerpt = mb_substr($body, 0, $maxExcerpt);

                $out[] = [
                    'url' => $u,
                    'status' => $resp->status(),
                    'bytes' => strlen($body),
                    'excerpt' => $excerpt,
                ];
            } catch (\Throwable $e) {
                $out[] = [
                    'url' => $u,
                    'status' => null,
                    'bytes' => 0,
                    'excerpt' => 'ERROR: '.$e->getMessage(),
                ];
            }
        }

        return $out;
    }

    /**
     * @param array<int, array{url:string, status:int|null, bytes:int, excerpt:string}> $downloaded
     * @param array<int, string> $inline
     */
    private function buildJsContextBlock(array $downloaded, array $inline): string
    {
        $parts = [];

        if (! empty($downloaded)) {
            $buf = "=== SORGENTI JS (script src scaricati - estratti) ===\n";
            foreach ($downloaded as $s) {
                $u = $s['url'];
                $status = $s['status'] === null ? 'ERR' : (string) $s['status'];
                $buf .= "\n--- {$u} (HTTP {$status}) ---\n";
                $buf .= $s['excerpt'];
                $buf .= "\n";
            }
            $parts[] = $buf;
        }

        if (! empty($inline)) {
            $buf = "=== JS INLINE (estratti) ===\n";
            foreach ($inline as $i => $txt) {
                $buf .= "\n--- inline #".($i + 1)." ---\n";
                $buf .= mb_substr($txt, 0, 10000);
                $buf .= "\n";
            }
            $parts[] = $buf;
        }

        $joined = implode("\n\n", $parts);

        // hard cap totale
        return mb_substr($joined, 0, 120000);
    }

    private function resolveUrl(string $pageUrl, string $src): string
    {
        $src = trim($src);
        if ($src === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $src) === 1) {
            return $src;
        }

        if (str_starts_with($src, '//')) {
            $schemeRaw = parse_url($pageUrl, PHP_URL_SCHEME);
            $scheme = is_string($schemeRaw) ? $schemeRaw : 'https';

            return $scheme.':'.$src;
        }

        $schemeRaw = parse_url($pageUrl, PHP_URL_SCHEME);
        $hostRaw = parse_url($pageUrl, PHP_URL_HOST);
        $scheme = is_string($schemeRaw) ? $schemeRaw : 'https';
        $host = is_string($hostRaw) ? $hostRaw : '';
        $port = parse_url($pageUrl, PHP_URL_PORT);
        $base = $scheme.'://'.$host.(is_int($port) ? ':'.$port : '');

        if (str_starts_with($src, '/')) {
            return rtrim($base, '/').$src;
        }

        $pathRaw = parse_url($pageUrl, PHP_URL_PATH);
        $path = is_string($pathRaw) ? $pathRaw : '/';
        $dir = rtrim(dirname($path), '/');

        return rtrim($base, '/').($dir ? '/'.$dir : '').'/'.ltrim($src, '/');
    }

    private function loadHtml(string $html): ?DOMDocument
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $ok = $dom->loadHTML($html);
        libxml_clear_errors();

        return $ok ? $dom : null;
    }
}
