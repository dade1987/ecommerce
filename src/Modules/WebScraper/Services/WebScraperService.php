<?php

namespace Modules\WebScraper\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Modules\WebScraper\Contracts\ScraperInterface;
use Modules\WebScraper\Contracts\ParserInterface;
use Modules\WebScraper\Models\ScrapedPage;

class WebScraperService implements ScraperInterface
{
    protected Client $httpClient;
    protected ParserInterface $parser;
    protected array $config;
    
    /**
     * Cache for verified domains to avoid repeated database queries
     * Format: ['domain' => true/false, 'www.domain' => true/false]
     * @var array<string, bool>
     */
    protected static array $domainCache = [];

    /**
     * Get TTL (seconds) for persistent domain existence cache.
     */
    protected function getDomainExistsCacheTtl(): int
    {
        return (int) config('webscraper.rag.domain_exists_cache_ttl', 60 * 60 * 24 * 7); // 7 days
    }

    /**
     * Persistent (Cache) + in-process (static) cached existence check for a domain and its www-variant.
     * Uses ONE Mongo query on cache miss to populate both variants.
     *
     * @return array{plain:string,www:string,exists_plain:bool,exists_www:bool,cache_hit_plain:bool,cache_hit_www:bool}
     */
    protected function getDomainExistenceCached(string $domain): array
    {
        $domain = strtolower(trim($domain));
        $domain = rtrim($domain, '. ');

        // Normalize to "plain" + "www" variants (without duplicating www.www.)
        $plain = str_starts_with($domain, 'www.') ? substr($domain, 4) : $domain;
        $www = 'www.' . $plain;

        $plainKey = 'webscraper:domain_exists:' . $plain;
        $wwwKey = 'webscraper:domain_exists:' . $www;

        // 1) In-process cache first
        $existsPlain = self::$domainCache[$plain] ?? null;
        $existsWww = self::$domainCache[$www] ?? null;

        // 2) Persistent cache
        $cacheHitPlain = false;
        $cacheHitWww = false;

        if ($existsPlain === null && Cache::has($plainKey)) {
            $cacheHitPlain = true;
            $existsPlain = (bool) Cache::get($plainKey);
            self::$domainCache[$plain] = $existsPlain;
        }

        if ($existsWww === null && Cache::has($wwwKey)) {
            $cacheHitWww = true;
            $existsWww = (bool) Cache::get($wwwKey);
            self::$domainCache[$www] = $existsWww;
        }

        // If both resolved, return
        if ($existsPlain !== null && $existsWww !== null) {
            return [
                'plain' => $plain,
                'www' => $www,
                'exists_plain' => (bool) $existsPlain,
                'exists_www' => (bool) $existsWww,
                'cache_hit_plain' => $cacheHitPlain,
                'cache_hit_www' => $cacheHitWww,
            ];
        }

        // 3) Cache miss: one DB query to populate both
        $ttl = $this->getDomainExistsCacheTtl();
        $dbResult = \Modules\WebScraper\Models\WebscraperPage::where('domain', $plain)
            ->orWhere('domain', $www)
            ->select('domain')
            ->limit(2)
            ->get();

        $foundDomains = $dbResult->pluck('domain')->toArray();
        $existsPlain = in_array($plain, $foundDomains, true);
        $existsWww = in_array($www, $foundDomains, true);

        // Persist + in-process cache
        Cache::put($plainKey, $existsPlain, $ttl);
        Cache::put($wwwKey, $existsWww, $ttl);
        self::$domainCache[$plain] = $existsPlain;
        self::$domainCache[$www] = $existsWww;

        return [
            'plain' => $plain,
            'www' => $www,
            'exists_plain' => $existsPlain,
            'exists_www' => $existsWww,
            'cache_hit_plain' => $cacheHitPlain,
            'cache_hit_www' => $cacheHitWww,
        ];
    }

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
     * Extract domain from query if it contains site: prefix or a valid domain
     * Example: "academy site:prtspa.com" -> "prtspa.com"
     * Example: "academy prtspa.com" -> "prtspa.com"
     * Example: "cerca site:example.co.uk" -> "example.co.uk"
     * Example: "info subdomain.example.com" -> "subdomain.example.com"
     *
     * @param string $query Search query
     * @return string|null Domain if found, null otherwise
     */
    protected function extractDomainFromQuery(string $query): ?string
    {
        // First, try to match site: prefix (priority)
        if (preg_match('/\bsite:([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*\.[a-z]{2,})\b/i', $query, $matches)) {
            $domain = strtolower(trim($matches[1]));
            
            // Remove trailing dots or spaces
            $domain = rtrim($domain, '. ');
            
            if (empty($domain)) {
                return null;
            }
            
            $existence = $this->getDomainExistenceCached($domain);

            // Prefer www if it exists
            if (!str_starts_with($domain, 'www.') && $existence['exists_www']) {
                return $existence['www'];
            }

            return $existence['plain'];
        }

        // If no site: prefix, try to find a valid domain in the query
        // Pattern matches valid domains: subdomain.domain.tld (e.g., prtspa.com, www.example.co.uk)
        // Must be surrounded by word boundaries or spaces to avoid matching partial words
        if (preg_match('/\b([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*\.[a-z]{2,})\b/i', $query, $matches)) {
            $domain = strtolower(trim($matches[1]));
            
            // Remove trailing dots or spaces
            $domain = rtrim($domain, '. ');
            
            if (empty($domain)) {
                return null;
            }
            
            $existence = $this->getDomainExistenceCached($domain);

            // Check if domain exists (either version)
            if (!$existence['exists_plain'] && !$existence['exists_www']) {
                return null;
            }
            
            // Normalize: add www. if missing (for consistency with indexed data)
            if (!str_starts_with($domain, 'www.') && $existence['exists_www']) {
                return $existence['www'];
            }

            return $existence['plain'];
        }

        return null;
    }

    /**
     * Remove site: prefix or domain from query
     * Example: "academy site:prtspa.com" -> "academy"
     * Example: "academy prtspa.com" -> "academy"
     * Example: "site:example.com cerca info" -> "cerca info"
     * Example: "test site:domain.co.uk altro" -> "test altro"
     * Example: "test domain.co.uk altro" -> "test altro"
     *
     * @param string $query Search query
     * @return string Query without site: prefix or domain
     */
    protected function removeSitePrefixFromQuery(string $query): string
    {
        // First, remove site:domain pattern from query (supports any valid domain)
        $sitePattern = '/\s*site:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*\.[a-z]{2,}\s*/i';
        $cleaned = preg_replace($sitePattern, ' ', $query);
        
        // Then, try to remove standalone domains (must verify they exist in database)
        // Extract potential domain first
        if (preg_match('/\b([a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*\.[a-z]{2,})\b/i', $cleaned, $matches)) {
            $potentialDomain = strtolower(trim($matches[1]));
            $potentialDomain = rtrim($potentialDomain, '. ');
            
            $existence = $this->getDomainExistenceCached($potentialDomain);

            if ($existence['exists_plain'] || $existence['exists_www']) {
                // Remove the domain from query
                $domainPattern = '/\b' . preg_quote($potentialDomain, '/') . '\b/i';
                $cleaned = preg_replace($domainPattern, ' ', $cleaned);
            }
        }
        
        // Clean up multiple spaces
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        return trim($cleaned);
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

            // Log preview of content to verify footer is included
            $contentPreview = substr($result['content']['main'], -1000); // Last 1000 chars (likely contains footer)
            Log::channel('webscraper')->info('WebScraper: Using full content for AI analysis', [
                'content_length' => strlen($result['content']['main']),
                'footer_preview' => $contentPreview,
            ]);
        } else {
            // Keep clean main content (footer removed)
            Log::channel('webscraper')->info('WebScraper: Using clean main content for AI analysis');
        }

        return $result;
    }

    /**
     * Scrape a single URL (alias for scrape method, used by indexer)
     *
     * @param string $url URL to scrape
     * @param array $options Scraping options
     * @return array Scraped data
     */
    public function scrapeSingleUrl(string $url, array $options = []): array
    {
        return $this->scrape($url, $options);
    }

    /**
     * Search site with RAG (Retrieval-Augmented Generation)
     *
     * This method attempts to answer questions using pre-indexed content (fast & cheap).
     * Falls back to traditional scraping if no indexed content is found.
     *
     * @param string $url Base URL of the site
     * @param string $query User's question
     * @param array $options Options: 'force_reindex', 'ttl_days', 'top_k', 'min_similarity'
     * @return array Result with answer, sources, and metadata
     */
    public function searchWithRag(string $url, string $query, array $options = []): array
    {
        $startTime = microtime(true);
        Log::channel('webscraper')->info('WebScraper: searchWithRag called', [
            'url' => $url,
            'query' => $query,
            'options' => $options,
        ]);

        // Check if query contains site: prefix to override domain
        $originalQuery = $query;
        $extractStart = microtime(true);
        $domainFromQuery = $this->extractDomainFromQuery($query);
        $extractTime = round((microtime(true) - $extractStart) * 1000, 2);
        
        if ($extractTime > 100) {
            Log::channel('webscraper')->warning('WebScraper: extractDomainFromQuery took too long', [
                'time_ms' => $extractTime,
                'query' => $query,
            ]);
        }
        
        if ($domainFromQuery) {
            // Use domain from query, remove site: prefix from query
            $domain = $domainFromQuery;
            $removeStart = microtime(true);
            $query = $this->removeSitePrefixFromQuery($query);
            $removeTime = round((microtime(true) - $removeStart) * 1000, 2);
            
            if ($removeTime > 100) {
                Log::channel('webscraper')->warning('WebScraper: removeSitePrefixFromQuery took too long', [
                    'time_ms' => $removeTime,
                    'original_query' => $originalQuery,
                ]);
            }
            
            Log::channel('webscraper')->info('WebScraper: Domain extracted from query', [
                'domain' => $domain,
                'original_query' => $originalQuery,
                'cleaned_query' => $query,
                'extract_time_ms' => $extractTime,
                'remove_time_ms' => $removeTime,
            ]);
        } else {
            // Normalize domain: add www. if missing (for consistency with indexed data)
            $domain = parse_url($url, PHP_URL_HOST);
            if ($domain && !str_starts_with($domain, 'www.')) {
                // Check if www version exists in database, use it
                $wwwDomain = 'www.' . $domain;
                $checkStart = microtime(true);

                $existence = $this->getDomainExistenceCached($domain);
                $existsWithWww = $existence['exists_www'];
                $checkTime = round((microtime(true) - $checkStart) * 1000, 2);
                
                if ($checkTime > 100) {
                    Log::channel('webscraper')->warning('WebScraper: Domain check took too long', [
                        'time_ms' => $checkTime,
                        'domain' => $wwwDomain,
                        'cache_hit_persistent_www' => $existence['cache_hit_www'],
                        'cache_hit_in_process_www' => array_key_exists($existence['www'], self::$domainCache),
                    ]);
                }
                
                if ($existsWithWww) {
                    $domain = $existence['www'];
                    Log::channel('webscraper')->info('WebScraper: Normalized domain to www version', [
                        'original' => parse_url($url, PHP_URL_HOST),
                        'normalized' => $domain,
                        'check_time_ms' => $checkTime,
                    ]);
                }
            }
        }
        
        $prepTime = round((microtime(true) - $startTime) * 1000, 2);
        if ($prepTime > 500) {
            Log::channel('webscraper')->warning('WebScraper: Preparation phase took too long', [
                'time_ms' => $prepTime,
                'extract_time_ms' => $extractTime ?? 0,
                'remove_time_ms' => $removeTime ?? 0,
            ]);
        }

        // Step 1: Try HYBRID search first (combines vector + text search with RRF)
        try {
            $hybridSearch = app(\Modules\WebScraper\Services\HybridSearchService::class);

            // Perform hybrid search (vector + text with RRF)
            $topK = $options['top_k'] ?? 10;
            $hybridResult = $hybridSearch->search(
                query: $query,
                domain: $domain,
                options: ['topK' => $topK]
            );

            // If hybrid search found relevant chunks, generate answer using ClientSiteQaService
            if ($hybridResult['count'] > 0) {
                Log::channel('webscraper')->info('WebScraper: Hybrid search successful', [
                    'results_count' => $hybridResult['count'],
                    'method' => $hybridResult['method'],
                    'vector_results' => $hybridResult['stats']['vector_results'],
                    'text_results' => $hybridResult['stats']['text_results'],
                ]);

                // Build context from hybrid search results
                $contextParts = [];
                foreach ($hybridResult['results'] as $index => $result) {
                    $contextParts[] = sprintf(
                        "[Source %d - %s (RRF Score: %.4f)]\nTitle: %s\nURL: %s\n\n%s\n",
                        $index + 1,
                        $result['domain'] ?? 'Unknown',
                        $result['rrf_score'],
                        $result['title'] ?? 'No title',
                        $result['url'] ?? 'No URL',
                        $result['content']
                    );
                }
                $context = implode("\n---\n\n", $contextParts);

                // Use same prompt as ClientSiteQaService
                $systemPrompt = <<<EOT
Sei un assistente AI che risponde a domande basandoti ESCLUSIVAMENTE sulle informazioni fornite nel contesto.

REGOLE:
1. Rispondi SOLO usando le informazioni presenti nel contesto
2. Se il contesto non contiene informazioni sufficienti, dillo chiaramente
3. Cita le fonti quando possibile (es. "Secondo [Source 1]...")
4. Sii conciso ma completo
5. Usa un tono professionale e amichevole
6. Rispondi in italiano

CONTESTO:
{$context}
EOT;

                try {
                    // Check for Groq first, then vLLM, then OpenAI
                    $groqApiKey = (string) config('services.groq.key', '');
                    $groqBaseUri = (string) config('services.groq.base_uri', 'https://api.groq.com/openai/v1');
                    $groqModel = (string) config('services.groq.model', 'llama-3.1-70b-versatile');

                    $vllmBaseUri = (string) config('services.vllm.base_uri', '');
                    $vllmApiKey = (string) config('services.vllm.key', '');
                    $vllmModel = (string) config('services.vllm.model', 'gpt-3.5-turbo');

                    $useHttp = false;
                    $httpBaseUri = '';
                    $httpApiKey = '';
                    $httpModel = '';

                    if ($groqApiKey !== '') {
                        // Use Groq OpenAI-compatible endpoint via HTTP
                        $useHttp = true;
                        $httpBaseUri = rtrim($groqBaseUri, '/');
                        $httpApiKey = $groqApiKey;
                        $httpModel = $groqModel;

                        Log::channel('webscraper')->info('WebScraper: Using Groq for hybrid search answer', [
                            'base_uri' => $httpBaseUri,
                            'model' => $httpModel,
                        ]);
                    } elseif ($vllmBaseUri !== '') {
                        // Use vLLM OpenAI-compatible endpoint via HTTP
                        $useHttp = true;
                        $httpBaseUri = rtrim($vllmBaseUri, '/');
                        $httpApiKey = $vllmApiKey;
                        $httpModel = $vllmModel;

                        Log::channel('webscraper')->info('WebScraper: Using vLLM for hybrid search answer', [
                            'base_uri' => $httpBaseUri,
                            'model' => $httpModel,
                        ]);
                    } else {
                        // Fallback to OpenAI
                        $apiKey = config('services.openai.key');
                        $client = \OpenAI::client($apiKey);
                        $vllmModel = 'gpt-3.5-turbo';

                        Log::channel('webscraper')->info('WebScraper: Using OpenAI for hybrid search answer');
                    }

                    if ($useHttp) {
                        // Use HTTP directly for Groq/vLLM
                        $response = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $httpApiKey,
                            'Content-Type' => 'application/json',
                        ])->timeout(60)->post($httpBaseUri . '/chat/completions', [
                            'model' => $httpModel,
                            'messages' => [
                                ['role' => 'system', 'content' => $systemPrompt],
                                ['role' => 'user', 'content' => $query],
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 1000,
                        ]);

                        if (!$response->successful()) {
                            throw new Exception('API error: ' . $response->body());
                        }

                        $result = $response->json();
                        $answer = $result['choices'][0]['message']['content'] ?? '';
                    } else {
                        // Use OpenAI client
                        $response = $client->chat()->create([
                            'model' => $vllmModel,
                            'messages' => [
                                ['role' => 'system', 'content' => $systemPrompt],
                                ['role' => 'user', 'content' => $query],
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 1000,
                        ]);

                        $answer = $response->choices[0]->message->content ?? '';
                    }

                    // Extract unique sources
                    $sources = [];
                    $seenUrls = [];
                    foreach ($hybridResult['results'] as $result) {
                        $url = $result['url'] ?? '';
                        if (!in_array($url, $seenUrls)) {
                            $sources[] = [
                                'url' => $url,
                                'title' => $result['title'] ?? 'No title',
                                'domain' => $result['domain'] ?? 'Unknown',
                                'rrf_score' => $result['rrf_score'],
                            ];
                            $seenUrls[] = $url;
                        }
                    }

                    $result = [
                        'success' => true,
                        'method' => 'hybrid_rag', // New method identifier
                        'answer' => trim($answer),
                        'sources' => $sources,
                        'chunks_found' => $hybridResult['count'],
                        'search_stats' => $hybridResult['stats'],
                        'from_cache' => false,
                    ];

                    // Salva la ricerca nel database WebsiteSearch se team_id è fornito
                    $this->saveWebsiteSearch($url, $query, $result, $options);

                    return $result;

                } catch (\Exception $e) {
                    Log::channel('webscraper')->error('WebScraper: LLM answer generation failed', [
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            }

            Log::channel('webscraper')->info('WebScraper: No hybrid search results, falling back to scraping');

        } catch (\Exception $e) {
            Log::channel('webscraper')->warning('WebScraper: Hybrid search failed, falling back to scraping', [
                'error' => $e->getMessage(),
            ]);
        }

        // Step 2: Fallback to cached search (checks MySQL cache first, then scrapes if needed)
        Log::channel('webscraper')->info('WebScraper: RAG failed, checking search_result_cache before scraping');

        try {
            // Use SearchResultCacheService (checks cache → semantic similarity → scraping)
            $cacheService = app(SearchResultCacheService::class);
            $maxPages = $options['max_pages'] ?? 10;
            $cachedResult = $cacheService->getCachedOrSearch($url, $query, $maxPages);

            // Extract search results from cache service response
            $searchResults = [
                'query' => $cachedResult['query'],
                'pages_visited' => $cachedResult['pages_visited'],
                'results' => $cachedResult['results'],
            ];
            $fromCache = $cachedResult['from_cache'] ?? false;

            Log::channel('webscraper')->info('WebScraper: Search completed', [
                'from_cache' => $fromCache,
                'match_type' => $cachedResult['match_type'] ?? 'unknown',
                'results_count' => count($searchResults['results']),
            ]);

            if (empty($searchResults['results'])) {
                return [
                    'success' => false,
                    'method' => 'scraping',
                    'answer' => 'Non sono state trovate informazioni rilevanti su questo sito.',
                    'sources' => [],
                    'pages_visited' => $searchResults['pages_visited'] ?? 0,
                ];
            }

            // Step 3: Index found pages for future RAG queries
            $indexer = app(SiteIndexerService::class);
            $urls = array_column($searchResults['results'], 'url');
            $ttlDays = $options['ttl_days'] ?? 30;

            Log::channel('webscraper')->info('WebScraper: Indexing pages for future RAG', [
                'urls_count' => count($urls),
                'ttl_days' => $ttlDays,
            ]);

            // Index asynchronously in background (don't wait for completion)
            foreach ($urls as $pageUrl) {
                try {
                    $indexer->indexUrl($pageUrl, $ttlDays);
                } catch (\Exception $e) {
                    Log::channel('webscraper')->warning('WebScraper: Failed to index page', [
                        'url' => $pageUrl,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Step 4: Generate AI analysis from scraped content
            $analyzer = app(AiAnalyzerService::class);

            // Scrape each found URL
            $aggregatedData = [];
            foreach ($searchResults['results'] as $result) {
                $scrapedData = $this->scrape($result['url'], ['query' => $query]);
                if (!isset($scrapedData['error'])) {
                    $aggregatedData[] = $scrapedData;
                }
            }

            $analysis = $analyzer->searchMultiplePages($aggregatedData, $query);
            $aiAnalysisText = $analysis['analysis'] ?? 'Analisi completata';

            $sources = [];
            foreach ($searchResults['results'] as $result) {
                $sources[] = [
                    'url' => $result['url'],
                    'title' => $result['title'] ?? 'No title',
                    'domain' => $domain,
                ];
            }

            Log::channel('webscraper')->info('WebScraper: Traditional search completed', [
                'pages_visited' => $searchResults['pages_visited'],
                'results_found' => count($searchResults['results']),
            ]);

            $result = [
                'success' => true,
                'method' => 'scraping_with_indexing',
                'answer' => $aiAnalysisText,
                'sources' => $sources,
                'pages_visited' => $searchResults['pages_visited'],
                'results_found' => count($searchResults['results']),
                'indexed_for_future' => true,
            ];

            // Salva la ricerca nel database WebsiteSearch se team_id è fornito
            $this->saveWebsiteSearch($url, $query, $result, $options);

            return $result;

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('WebScraper: Traditional search failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'method' => 'error',
                'answer' => 'Si è verificato un errore durante la ricerca.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Index a URL for RAG system
     *
     * @param string $url URL to index
     * @param int|null $ttlDays Time-to-live in days (null = never expires)
     * @return array Result with indexing status
     */
    public function indexForRag(string $url, ?int $ttlDays = 30): array
    {
        Log::channel('webscraper')->info('WebScraper: indexForRag called', [
            'url' => $url,
            'ttl_days' => $ttlDays,
        ]);

        try {
            $indexer = app(SiteIndexerService::class);
            $page = $indexer->indexUrl($url, $ttlDays);

            return [
                'success' => true,
                'page_id' => $page->_id,
                'url' => $page->url,
                'chunks_created' => $page->chunk_count,
                'word_count' => $page->word_count,
                'indexed_at' => $page->indexed_at,
                'expires_at' => $page->expires_at,
            ];

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('WebScraper: Indexing failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get RAG indexing statistics
     *
     * @return array Statistics
     */
    public function getRagStats(): array
    {
        try {
            $indexer = app(SiteIndexerService::class);
            return $indexer->getStats();
        } catch (\Exception $e) {
            Log::channel('webscraper')->error('WebScraper: Failed to get RAG stats', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Salva la ricerca nel database WebsiteSearch
     *
     * @param string $url URL del sito cercato
     * @param string $query Query di ricerca
     * @param array $result Risultato della ricerca
     * @param array $options Opzioni (team_id, locale)
     */
    protected function saveWebsiteSearch(string $url, string $query, array $result, array $options = []): void
    {
        try {
            \App\Models\WebsiteSearch::create([
                'website' => $url,
                'query' => $query,
                'team_id' => $options['team_id'] ?? null,
                'locale' => $options['locale'] ?? 'it',
                'response' => $result['answer'] ?? null,
                'content_length' => strlen($result['answer'] ?? ''),
                'from_cache' => ($result['method'] === 'hybrid_rag' || ($result['from_cache'] ?? false)),
            ]);

            Log::channel('webscraper')->info('WebScraper: WebsiteSearch saved', [
                'url' => $url,
                'query' => $query,
                'team_id' => $options['team_id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::channel('webscraper')->error('WebScraper: Failed to save WebsiteSearch', [
                'error' => $e->getMessage(),
                'url' => $url,
                'query' => $query,
            ]);
        }
    }
}