<?php

namespace Modules\WebScraper\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Modules\WebScraper\Contracts\ScraperInterface;
use Modules\WebScraper\Contracts\ParserInterface;
use Modules\WebScraper\Models\ScrapedPage;

class WebScraperService implements ScraperInterface
{
    protected Client $httpClient;
    protected ParserInterface $parser;
    protected array $config;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
        $this->config = config('webscraper', []);

        // Select a random user agent from the configured list
        $userAgent = $this->getRandomUserAgent();

        $this->httpClient = new Client([
            'timeout' => $this->config['scraping']['timeout'] ?? 60,
            'connect_timeout' => 10,
            'allow_redirects' => [
                'max' => $this->config['scraping']['max_redirects'] ?? 5,
                'strict' => true,
                'referer' => true,
            ],
            'headers' => [
                'User-Agent' => $userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'it-IT,it;q=0.9,en-US;q=0.8,en;q=0.7',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Sec-Ch-Ua' => '"Google Chrome";v="131", "Chromium";v="131", "Not_A Brand";v="24"',
                'Sec-Ch-Ua-Mobile' => '?0',
                'Sec-Ch-Ua-Platform' => '"macOS"',
                'DNT' => '1',
                'Cache-Control' => 'max-age=0',
            ],
            'cookies' => true, // Enable cookie jar for session persistence
            'verify' => false, // Disable SSL verification for problematic sites
        ]);

        Log::channel('webscraper')->debug('WebScraper: Using User-Agent', ['user_agent' => $userAgent]);
    }

    /**
     * Get a random user agent from the configured list
     */
    protected function getRandomUserAgent(): string
    {
        $userAgents = $this->config['scraping']['user_agents'] ?? [];

        if (empty($userAgents)) {
            // Fallback to single user_agent config
            return $this->config['scraping']['user_agent'] ?? 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';
        }

        // Return a random user agent from the array
        return $userAgents[array_rand($userAgents)];
    }

    /**
     * Scrape a website and return structured data
     */
    public function scrape(string $url, array $options = []): array
    {
        // Validate URL
        if (!$this->isValidUrl($url)) {
            return ['error' => 'Invalid URL provided'];
        }

        // Check if domain is blocked
        if ($this->isBlockedDomain($url)) {
            return ['error' => 'Domain is blocked'];
        }

        // Check cache first
        if ($this->config['cache']['enabled'] ?? true) {
            $cached = $this->getCached($url);
            if ($cached !== null) {
                Log::channel('webscraper')->info('WebScraper: Using cached data', ['url' => $url]);

                // Apply query-based content selection to cached data
                if (isset($options['query'])) {
                    $cached = $this->selectContentByQuery($cached, $options['query']);
                }

                return $cached;
            }
        }

        try {
            Log::channel('webscraper')->info('WebScraper: Starting scrape', ['url' => $url]);

            // Check if we should use headless browser for this URL
            $useBrowser = BrowserScraperService::shouldUseBrowserScraping($url);

            if ($useBrowser) {
                Log::channel('webscraper')->info('WebScraper: Using headless browser for anti-bot protected site', ['url' => $url]);

                $browserScraper = new BrowserScraperService();

                if (!$browserScraper->isAvailable()) {
                    Log::channel('webscraper')->warning('WebScraper: Headless browser not available, falling back to HTTP', ['url' => $url]);
                    $html = $this->fetchHtml($url);
                } else {
                    $browserResult = $browserScraper->scrape($url);

                    if (isset($browserResult['error'])) {
                        Log::channel('webscraper')->error('WebScraper: Browser scraping failed, falling back to HTTP', [
                            'url' => $url,
                            'error' => $browserResult['error'],
                        ]);
                        $html = $this->fetchHtml($url);
                    } else {
                        $html = $browserResult['html'];
                        Log::channel('webscraper')->info('WebScraper: Successfully fetched with headless browser', [
                            'url' => $url,
                            'html_length' => strlen($html),
                        ]);
                    }
                }
            } else {
                // Fetch HTML with standard HTTP client
                $html = $this->fetchHtml($url);
            }

            if (empty($html)) {
                return ['error' => 'Failed to fetch HTML content'];
            }

            // Parse HTML to extract structured data
            $parsedData = $this->parser->parse($html);

            // Extract ALL text content from raw HTML (for keyword search)
            $allContent = $this->parser->extractAllText($html);

            // Build result
            $result = [
                'url' => $url,
                'scraped_at' => now()->toIso8601String(),
                'metadata' => $parsedData['metadata'] ?? [],
                'content' => [
                    'main' => $parsedData['main_content'] ?? '',  // Clean content for AI
                    'full' => $allContent,  // Full content for keyword search
                    'headings' => $parsedData['headings'] ?? [],
                    'structured' => $parsedData['structured_content'] ?? [],
                ],
                'links' => $parsedData['links'] ?? [],
                'images' => $parsedData['images'] ?? [],
                'raw_html' => $html,  // Store raw HTML for menu extraction
                'raw_html_length' => strlen($html),
            ];

            // Apply content length limit
            $maxLength = $this->config['parsing']['max_content_length'] ?? 50000;
            if (strlen($result['content']['main']) > $maxLength) {
                $result['content']['main'] = substr($result['content']['main'], 0, $maxLength) . '...';
                $result['content_truncated'] = true;
            }

            // Cache result
            if ($this->config['cache']['enabled'] ?? true) {
                $this->cacheResult($url, $result);
            }

            Log::channel('webscraper')->info('WebScraper: Scrape completed', [
                'url' => $url,
                'main_length' => strlen($result['content']['main']),
                'full_length' => strlen($result['content']['full']),
            ]);

            // Apply query-based content selection
            if (isset($options['query'])) {
                $result = $this->selectContentByQuery($result, $options['query']);
            }

            return $result;

        } catch (GuzzleException $e) {
            Log::channel('webscraper')->error('WebScraper: HTTP error', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return ['error' => 'Failed to fetch website: ' . $e->getMessage()];

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('WebScraper: Scraping error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => 'An error occurred during scraping: ' . $e->getMessage()];
        }
    }

    /**
     * Get cached scraping result from SQLite
     */
    public function getCached(string $url): ?array
    {
        if (!($this->config['cache']['enabled'] ?? true)) {
            return null;
        }

        return ScrapedPage::getCached($url);
    }

    /**
     * Clear cache for a specific URL
     */
    public function clearCache(string $url): bool
    {
        return ScrapedPage::clearUrl($url);
    }

    /**
     * Crawl multiple pages from a website starting from the given URL
     * Returns array of scraped pages with their content
     */
    public function crawlSite(string $startUrl, int $maxPages = null): array
    {
        $maxPages = $maxPages ?? ($this->config['scraping']['max_pages'] ?? 10);

        Log::channel('webscraper')->info('crawlSite: Starting crawl', [
            'start_url' => $startUrl,
            'max_pages' => $maxPages,
        ]);

        $visited = [];
        $toVisit = [$startUrl];
        $results = [];
        $baseHost = parse_url($startUrl, PHP_URL_HOST);

        while (!empty($toVisit) && count($results) < $maxPages) {
            $currentUrl = array_shift($toVisit);

            // Skip if already visited
            if (in_array($currentUrl, $visited)) {
                continue;
            }

            // Mark as visited
            $visited[] = $currentUrl;

            try {
                // Scrape current page
                $scrapedData = $this->scrape($currentUrl);

                if (isset($scrapedData['error'])) {
                    Log::channel('webscraper')->warning('crawlSite: Error scraping page', [
                        'url' => $currentUrl,
                        'error' => $scrapedData['error'],
                    ]);
                    continue;
                }

                // Add to results
                $results[] = $scrapedData;

                Log::channel('webscraper')->info('crawlSite: Page scraped', [
                    'url' => $currentUrl,
                    'pages_scraped' => count($results),
                    'links_found' => count($scrapedData['links']),
                ]);

                // Extract and queue internal links
                foreach ($scrapedData['links'] as $link) {
                    $linkUrl = $this->normalizeUrl($link['url'], $currentUrl);

                    // Only follow links from the same domain
                    if ($linkUrl && !in_array($linkUrl, $visited) && !in_array($linkUrl, $toVisit)) {
                        $linkHost = parse_url($linkUrl, PHP_URL_HOST);
                        if ($linkHost === $baseHost) {
                            $toVisit[] = $linkUrl;
                        }
                    }
                }

                // Add delay between requests
                $delay = $this->config['scraping']['delay'] ?? 1000;
                usleep($delay * 1000); // Convert to microseconds

            } catch (\Exception $e) {
                Log::channel('webscraper')->error('crawlSite: Exception during crawl', [
                    'url' => $currentUrl,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        Log::channel('webscraper')->info('crawlSite: Crawl completed', [
            'start_url' => $startUrl,
            'pages_scraped' => count($results),
            'pages_visited' => count($visited),
        ]);

        return $results;
    }

    /**
     * Normalize a URL (handle relative URLs, remove fragments, etc.)
     */
    public function normalizeUrl(string $url, string $baseUrl): ?string
    {
        // Remove fragment
        $url = preg_replace('/#.*$/', '', $url);

        // Skip empty, javascript:, mailto:, tel:, etc.
        if (empty($url) ||
            preg_match('/^(javascript|mailto|tel|#):/i', $url) ||
            $url === '#') {
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

    /**
     * Fetch HTML content from URL
     */
    protected function fetchHtml(string $url): string
    {
        $response = $this->httpClient->get($url);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('HTTP status code: ' . $response->getStatusCode());
        }

        return $response->getBody()->getContents();
    }

    /**
     * Cache scraping result in SQLite
     */
    protected function cacheResult(string $url, array $result): void
    {
        $ttl = config('webscraper.database.cache_ttl', 86400); // 24 hours

        ScrapedPage::storeCache($url, $result, $ttl);
    }

    /**
     * Validate URL
     */
    protected function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if domain is blocked
     */
    protected function isBlockedDomain(string $url): bool
    {
        $blockedDomains = $this->config['blocked_domains'] ?? [];

        $host = parse_url($url, PHP_URL_HOST);

        foreach ($blockedDomains as $blocked) {
            if (str_contains($host, $blocked)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if domain is allowed (if whitelist is set)
     */
    protected function isAllowedDomain(string $url): bool
    {
        $allowedDomains = $this->config['allowed_domains'] ?? [];

        // If no whitelist, allow all
        if (empty($allowedDomains)) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);

        foreach ($allowedDomains as $allowed) {
            if (str_contains($host, $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Select appropriate content based on query keywords
     * If query contains contact-related terms, use full content (includes footer)
     * Otherwise use clean main content
     */
    protected function selectContentByQuery(array $result, string $query): array
    {
        $queryLower = mb_strtolower($query);

        // Keywords that indicate we need footer/header content
        $contactKeywords = [
            'contatt', 'telefon', 'email', 'indirizzo', 'address', 'phone',
            'contact', 'dove', 'where', 'ubicazione', 'location', 'sede',
            'mail', 'chiamare', 'call', 'scrivere', 'write', 'orari',
            'hours', 'apertura', 'opening', 'chiusura', 'closing'
        ];

        $needsFooter = false;
        foreach ($contactKeywords as $keyword) {
            if (stripos($queryLower, $keyword) !== false) {
                $needsFooter = true;
                Log::channel('webscraper')->info('WebScraper: Query requires footer content', [
                    'query' => $query,
                    'matched_keyword' => $keyword
                ]);
                break;
            }
        }

        if ($needsFooter) {
            // Use full content that includes footer, header, nav
            $result['content']['main'] = $result['content']['full'];
            Log::channel('webscraper')->info('WebScraper: Using full content for AI analysis');
        } else {
            // Keep clean main content (footer removed)
            Log::channel('webscraper')->info('WebScraper: Using clean main content for AI analysis');
        }

        return $result;
    }
}