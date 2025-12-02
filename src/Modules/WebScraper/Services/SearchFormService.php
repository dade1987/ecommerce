<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SearchFormService
{
    protected WebScraperService $scraper;
    protected HtmlParserService $parser;

    public function __construct(WebScraperService $scraper, HtmlParserService $parser)
    {
        $this->scraper = $scraper;
        $this->parser = $parser;
    }

    /**
     * Find and submit search form with query
     * Returns URLs found in search results
     */
    public function submitSearchForm(string $baseUrl, string $query): array
    {
        Log::channel('webscraper')->info('SearchFormService: Starting search form submission', [
            'base_url' => $baseUrl,
            'query' => $query,
        ]);

        // Step 1: Scrape homepage to find search forms
        $scrapedData = $this->scraper->scrape($baseUrl);

        if (isset($scrapedData['error'])) {
            Log::channel('webscraper')->error('SearchFormService: Failed to scrape base URL', ['error' => $scrapedData['error']]);
            return [];
        }

        // Step 2: Extract search forms
        $html = $this->fetchRawHtml($baseUrl);
        $searchForms = $this->parser->extractSearchForms($html);

        if (empty($searchForms)) {
            Log::channel('webscraper')->info('SearchFormService: No search forms found on page');
            return [];
        }

        Log::channel('webscraper')->info('SearchFormService: Found search forms', ['count' => count($searchForms)]);

        // Step 3: Submit to each search form and collect results
        $allResults = [];

        foreach ($searchForms as $form) {
            try {
                $results = $this->submitForm($baseUrl, $form, $query);
                $allResults = array_merge($allResults, $results);
            } catch (\Exception $e) {
                Log::channel('webscraper')->warning('SearchFormService: Error submitting form', [
                    'form' => $form,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Remove duplicates
        $allResults = array_unique($allResults);

        Log::channel('webscraper')->info('SearchFormService: Search completed', [
            'query' => $query,
            'results_found' => count($allResults),
        ]);

        return array_values($allResults);
    }

    /**
     * Submit a single search form
     */
    protected function submitForm(string $baseUrl, array $form, string $query): array
    {
        $action = $form['action'];
        $method = $form['method'];
        $inputName = $form['input_name'];

        // Build full action URL
        if (empty($action)) {
            $actionUrl = $baseUrl;
        } elseif (preg_match('/^https?:\/\//i', $action)) {
            $actionUrl = $action;
        } else {
            // Relative URL
            $baseUrlParsed = parse_url($baseUrl);
            $scheme = $baseUrlParsed['scheme'];
            $host = $baseUrlParsed['host'];
            $port = $baseUrlParsed['port'] ?? null;

            $baseUrlNormalized = $scheme . '://' . $host;
            if ($port && $port !== 80 && $port !== 443) {
                $baseUrlNormalized .= ':' . $port;
            }

            if (strpos($action, '/') === 0) {
                $actionUrl = $baseUrlNormalized . $action;
            } else {
                $actionUrl = rtrim($baseUrl, '/') . '/' . $action;
            }
        }

        Log::channel('webscraper')->info('SearchFormService: Submitting form', [
            'action_url' => $actionUrl,
            'method' => $method,
            'input_name' => $inputName,
            'query' => $query,
        ]);

        // Prepare search parameters
        $searchParams = [
            $inputName => $query,
        ];

        try {
            // Submit form based on method
            if ($method === 'POST') {
                $response = Http::timeout(30)->asForm()->post($actionUrl, $searchParams);
            } else {
                // GET method
                $response = Http::timeout(30)->get($actionUrl, $searchParams);
            }

            if (!$response->successful()) {
                Log::channel('webscraper')->warning('SearchFormService: Form submission failed', [
                    'status' => $response->status(),
                    'url' => $actionUrl,
                ]);
                return [];
            }

            $searchResultHtml = $response->body();

            // Parse search results page
            return $this->extractResultUrls($searchResultHtml, $baseUrl);

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('SearchFormService: Exception during form submission', [
                'url' => $actionUrl,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Extract URLs from search results page
     */
    protected function extractResultUrls(string $html, string $baseUrl): array
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return [];
        }

        $urls = [];
        $anchors = $dom->getElementsByTagName('a');

        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');

            if (empty($href)) {
                continue;
            }

            // Skip non-content links
            if (preg_match('/^(#|javascript:|mailto:|tel:)/i', $href)) {
                continue;
            }

            // Normalize URL
            $normalizedUrl = $this->normalizeUrl($href, $baseUrl);

            if ($normalizedUrl) {
                $urls[] = $normalizedUrl;
            }
        }

        // Limit and deduplicate
        return array_slice(array_unique($urls), 0, 50);
    }

    /**
     * Fetch raw HTML
     */
    protected function fetchRawHtml(string $url): string
    {
        try {
            $response = Http::timeout(30)->get($url);
            return $response->body();
        } catch (\Exception $e) {
            Log::channel('webscraper')->error('SearchFormService: Failed to fetch raw HTML', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    /**
     * Load HTML into DOMDocument
     */
    protected function loadHtml(string $html): ?\DOMDocument
    {
        if (empty($html)) {
            return null;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        return $dom;
    }

    /**
     * Normalize URL
     */
    protected function normalizeUrl(string $url, string $baseUrl): ?string
    {
        // Remove fragment
        $url = preg_replace('/#.*$/', '', $url);

        if (empty($url)) {
            return null;
        }

        // Handle absolute URLs
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        // Handle protocol-relative URLs
        if (preg_match('/^\/\//i', $url)) {
            $baseScheme = parse_url($baseUrl, PHP_URL_SCHEME);
            return $baseScheme . ':' . $url;
        }

        // Handle absolute paths
        if (preg_match('/^\//', $url)) {
            $baseScheme = parse_url($baseUrl, PHP_URL_SCHEME);
            $baseHost = parse_url($baseUrl, PHP_URL_HOST);
            $basePort = parse_url($baseUrl, PHP_URL_PORT);

            $baseUrlNormalized = $baseScheme . '://' . $baseHost;
            if ($basePort && $basePort !== 80 && $basePort !== 443) {
                $baseUrlNormalized .= ':' . $basePort;
            }

            return $baseUrlNormalized . $url;
        }

        // Handle relative paths
        $basePath = parse_url($baseUrl, PHP_URL_PATH);
        $baseDir = dirname($basePath);
        if ($baseDir === '.') {
            $baseDir = '/';
        }

        $baseScheme = parse_url($baseUrl, PHP_URL_SCHEME);
        $baseHost = parse_url($baseUrl, PHP_URL_HOST);
        $basePort = parse_url($baseUrl, PHP_URL_PORT);

        $baseUrlNormalized = $baseScheme . '://' . $baseHost;
        if ($basePort && $basePort !== 80 && $basePort !== 443) {
            $baseUrlNormalized .= ':' . $basePort;
        }

        return $baseUrlNormalized . $baseDir . '/' . $url;
    }
}
