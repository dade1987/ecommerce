<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SitemapService
{
    protected WebScraperService $scraper;

    public function __construct(WebScraperService $scraper)
    {
        $this->scraper = $scraper;
    }

    /**
     * Fetch and parse sitemap.xml from a website
     * Returns array of URLs from the sitemap
     */
    public function parseSitemap(string $baseUrl): array
    {
        Log::channel('webscraper')->info('SitemapService: Fetching sitemap', ['base_url' => $baseUrl]);

        $sitemapUrls = $this->findSitemapUrl($baseUrl);
        $allUrls = [];

        foreach ($sitemapUrls as $sitemapUrl) {
            try {
                $urls = $this->fetchSitemapUrls($sitemapUrl);
                $allUrls = array_merge($allUrls, $urls);

                Log::channel('webscraper')->info('SitemapService: Parsed sitemap', [
                    'sitemap_url' => $sitemapUrl,
                    'urls_found' => count($urls),
                ]);

            } catch (\Exception $e) {
                Log::channel('webscraper')->warning('SitemapService: Error parsing sitemap', [
                    'sitemap_url' => $sitemapUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Remove duplicates
        $allUrls = array_unique($allUrls);

        Log::channel('webscraper')->info('SitemapService: Total URLs from sitemaps', ['total' => count($allUrls)]);

        return array_values($allUrls);
    }

    /**
     * Find sitemap URL(s) for a website
     * Tries common locations: /sitemap.xml, /sitemap_index.xml, robots.txt
     */
    protected function findSitemapUrl(string $baseUrl): array
    {
        $baseUrl = rtrim($baseUrl, '/');
        $sitemapUrls = [];

        // Try common sitemap locations
        $commonLocations = [
            '/sitemap.xml',
            '/sitemap_index.xml',
            '/sitemap-index.xml',
            '/sitemaps.xml',
            '/sitemap1.xml',
        ];

        foreach ($commonLocations as $location) {
            $url = $baseUrl . $location;
            if ($this->urlExists($url)) {
                $sitemapUrls[] = $url;
                Log::channel('webscraper')->info('SitemapService: Found sitemap', ['url' => $url]);
            }
        }

        // Check robots.txt for sitemap declaration
        $robotsSitemaps = $this->getSitemapsFromRobotsTxt($baseUrl);
        $sitemapUrls = array_merge($sitemapUrls, $robotsSitemaps);

        return array_unique($sitemapUrls);
    }

    /**
     * Get sitemap URLs from robots.txt
     */
    protected function getSitemapsFromRobotsTxt(string $baseUrl): array
    {
        $robotsUrl = rtrim($baseUrl, '/') . '/robots.txt';
        $sitemaps = [];

        try {
            $response = Http::timeout(10)->get($robotsUrl);

            if ($response->successful()) {
                $robotsTxt = $response->body();
                $lines = explode("\n", $robotsTxt);

                foreach ($lines as $line) {
                    if (preg_match('/^\s*Sitemap:\s*(.+)\s*$/i', $line, $matches)) {
                        $sitemapUrl = trim($matches[1]);
                        $sitemaps[] = $sitemapUrl;
                        Log::channel('webscraper')->info('SitemapService: Found sitemap in robots.txt', ['url' => $sitemapUrl]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::channel('webscraper')->debug('SitemapService: Could not fetch robots.txt', [
                'url' => $robotsUrl,
                'error' => $e->getMessage(),
            ]);
        }

        return $sitemaps;
    }

    /**
     * Fetch URLs from a sitemap XML file
     */
    protected function fetchSitemapUrls(string $sitemapUrl): array
    {
        $urls = [];

        try {
            $response = Http::timeout(30)->get($sitemapUrl);

            if (!$response->successful()) {
                return $urls;
            }

            $xml = $response->body();

            // Try to parse as XML
            libxml_use_internal_errors(true);
            $xmlObj = simplexml_load_string($xml);
            libxml_clear_errors();

            if ($xmlObj === false) {
                Log::channel('webscraper')->warning('SitemapService: Failed to parse sitemap XML', ['url' => $sitemapUrl]);
                return $urls;
            }

            // Register namespaces
            $namespaces = $xmlObj->getNamespaces(true);

            // Check if it's a sitemap index (contains other sitemaps)
            if (isset($xmlObj->sitemap)) {
                Log::channel('webscraper')->info('SitemapService: Found sitemap index', ['url' => $sitemapUrl]);

                foreach ($xmlObj->sitemap as $sitemap) {
                    $loc = (string)$sitemap->loc;
                    if (!empty($loc)) {
                        // Recursively fetch URLs from nested sitemap
                        $nestedUrls = $this->fetchSitemapUrls($loc);
                        $urls = array_merge($urls, $nestedUrls);
                    }
                }
            }
            // Regular sitemap with URLs
            elseif (isset($xmlObj->url)) {
                foreach ($xmlObj->url as $url) {
                    $loc = (string)$url->loc;
                    if (!empty($loc)) {
                        $urls[] = $loc;
                    }
                }
            }

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('SitemapService: Error fetching sitemap', [
                'url' => $sitemapUrl,
                'error' => $e->getMessage(),
            ]);
        }

        return $urls;
    }

    /**
     * Check if a URL exists (returns 200)
     */
    protected function urlExists(string $url): bool
    {
        try {
            $response = Http::timeout(5)->head($url);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Search URLs in sitemap for query relevance
     * Returns URLs that might contain the searched information
     */
    public function searchInSitemap(string $baseUrl, string $query): array
    {
        $sitemapUrls = $this->parseSitemap($baseUrl);

        if (empty($sitemapUrls)) {
            return [];
        }

        $keywords = $this->extractKeywords($query);
        $relevantUrls = [];

        foreach ($sitemapUrls as $url) {
            $urlLower = strtolower($url);

            foreach ($keywords as $keyword) {
                if (stripos($urlLower, $keyword) !== false) {
                    $relevantUrls[] = $url;
                    break;
                }
            }
        }

        Log::channel('webscraper')->info('SitemapService: Found relevant URLs in sitemap', [
            'base_url' => $baseUrl,
            'query' => $query,
            'total_urls' => count($sitemapUrls),
            'relevant_urls' => count($relevantUrls),
        ]);

        return array_slice($relevantUrls, 0, 20); // Limit to 20 most relevant
    }

    /**
     * Extract keywords from query (remove stop words)
     */
    protected function extractKeywords(string $query): array
    {
        $stopWords = ['il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una', 'del', 'della', 'dei', 'degli', 'delle', 'di', 'da', 'a', 'in', 'su', 'per', 'con', 'tra', 'fra', 'e', 'ed', 'o', 'od'];

        $words = preg_split('/\s+/', strtolower($query));
        $keywords = [];

        foreach ($words as $word) {
            $word = trim($word, '.,!?;:');
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return $keywords;
    }
}