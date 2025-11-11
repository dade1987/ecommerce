<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IntelligentCrawlerService
{
    protected WebScraperService $scraper;
    protected HtmlParserService $parser;
    protected AiAnalyzerService $aiAnalyzer;
    protected SitemapService $sitemapService;
    protected SearchFormService $searchFormService;
    protected array $visitedUrls = [];
    protected array $foundResults = [];

    public function __construct(
        WebScraperService $scraper,
        HtmlParserService $parser,
        AiAnalyzerService $aiAnalyzer,
        SitemapService $sitemapService,
        SearchFormService $searchFormService
    ) {
        $this->scraper = $scraper;
        $this->parser = $parser;
        $this->aiAnalyzer = $aiAnalyzer;
        $this->sitemapService = $sitemapService;
        $this->searchFormService = $searchFormService;
    }

    /**
     * Intelligent search guided by website menu structure
     * Now includes sitemap and search form strategies!
     */
    public function intelligentSearch(string $startUrl, string $query, int $maxDepth = 3): array
    {
        $maxPages = config('webscraper.scraping.max_pages', 10);

        Log::channel('webscraper')->info('IntelligentCrawler: Starting intelligent search', [
            'start_url' => $startUrl,
            'query' => $query,
            'max_depth' => $maxDepth,
            'max_pages' => $maxPages,
        ]);

        $this->visitedUrls = [];
        $this->foundResults = [];

        // STRATEGY 1: Try search form submission (fastest and most accurate!)
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 1 - Trying search form submission');
        $searchFormUrls = $this->searchFormService->submitSearchForm($startUrl, $query);

        if (!empty($searchFormUrls)) {
            Log::channel('webscraper')->info('IntelligentCrawler: Search form found results!', [
                'urls_found' => count($searchFormUrls),
            ]);

            // Scrape the search result pages
            $maxPages = config('webscraper.scraping.max_pages', 10);
            foreach (array_slice($searchFormUrls, 0, $maxPages) as $resultUrl) {
                if (!in_array($resultUrl, $this->visitedUrls)) {
                    $this->scrapeSinglePage($resultUrl, $query, 0, 'search_form');
                }
            }
        }

        // STRATEGY 2: Check sitemap for relevant URLs (keyword-based)
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 2 - Searching in sitemap (keyword filter)');
        $sitemapUrls = $this->sitemapService->searchInSitemap($startUrl, $query);

        if (!empty($sitemapUrls)) {
            Log::channel('webscraper')->info('IntelligentCrawler: Sitemap found relevant URLs!', [
                'urls_found' => count($sitemapUrls),
            ]);

            // Scrape the sitemap URLs
            $maxPages = config('webscraper.scraping.max_pages', 10);
            foreach (array_slice($sitemapUrls, 0, $maxPages) as $sitemapUrl) {
                if (!in_array($sitemapUrl, $this->visitedUrls)) {
                    $this->scrapeSinglePage($sitemapUrl, $query, 0, 'sitemap_keyword');
                }
            }
        }

        // STRATEGY 3: Menu-guided recursive crawling
        // First, ALWAYS scrape the homepage to check footer and main content
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 3 - Analyzing homepage');
        if (!in_array($startUrl, $this->visitedUrls)) {
            $this->scrapeSinglePage($startUrl, $query, 0, 'homepage');
        }

        // Then proceed with menu-guided crawling
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 3 - Menu-guided crawling');
        $this->searchRecursive($startUrl, $query, 0, $maxDepth);

        // STRATEGY 4 (FALLBACK): If we have less than 30 results, scrape more pages from sitemap
        $minResults = 30; // Minimum number of results before we're satisfied
        if (count($this->foundResults) < $minResults) {
            Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 4 - Found few results, trying all sitemap URLs', [
                'current_results' => count($this->foundResults),
                'min_required' => $minResults,
            ]);
            $allSitemapUrls = $this->sitemapService->parseSitemap($startUrl);

            if (!empty($allSitemapUrls)) {
                Log::channel('webscraper')->info('IntelligentCrawler: Found sitemap with all URLs', [
                    'total_urls' => count($allSitemapUrls),
                ]);

                $remainingPages = $maxPages - count($this->visitedUrls);
                foreach (array_slice($allSitemapUrls, 0, $remainingPages) as $sitemapUrl) {
                    if (!in_array($sitemapUrl, $this->visitedUrls)) {
                        $this->scrapeSinglePage($sitemapUrl, $query, 0, 'sitemap_full');

                        // Stop if we have enough results
                        if (count($this->foundResults) >= $minResults) {
                            Log::channel('webscraper')->info('IntelligentCrawler: Reached minimum results, stopping sitemap scan');
                            break;
                        }
                    }
                }
            }
        }

        Log::channel('webscraper')->info('IntelligentCrawler: Search completed', [
            'pages_visited' => count($this->visitedUrls),
            'results_found' => count($this->foundResults),
        ]);

        return [
            'query' => $query,
            'pages_visited' => count($this->visitedUrls),
            'results' => $this->foundResults,
        ];
    }

    /**
     * Scrape a single page and check for query match
     */
    protected function scrapeSinglePage(string $url, string $query, int $depth, string $source): void
    {
        // Skip if already visited
        if (in_array($url, $this->visitedUrls)) {
            return;
        }

        $this->visitedUrls[] = $url;

        Log::channel('webscraper')->info('IntelligentCrawler: Scraping page', [
            'url' => $url,
            'source' => $source,
        ]);

        $scrapedData = $this->scraper->scrape($url);

        if (isset($scrapedData['error'])) {
            Log::channel('webscraper')->warning('IntelligentCrawler: Error scraping page', [
                'url' => $url,
                'error' => $scrapedData['error'],
            ]);
            return;
        }

        // Check if content matches query
        $contentMatch = $this->checkContentMatch($scrapedData, $query);
        if ($contentMatch) {
            Log::channel('webscraper')->info('IntelligentCrawler: Found match!', [
                'url' => $url,
                'source' => $source,
            ]);

            $this->foundResults[] = [
                'url' => $url,
                'title' => $scrapedData['metadata']['title'] ?? 'Untitled',
                'match_type' => $source,
                'content_excerpt' => substr($scrapedData['content']['main'], 0, 500),
                'depth' => $depth,
            ];
        }
    }

    /**
     * Recursive search through pages following menu links
     */
    protected function searchRecursive(string $url, string $query, int $depth, int $maxDepth): void
    {
        // Stop if max depth reached
        if ($depth > $maxDepth) {
            Log::channel('webscraper')->debug('IntelligentCrawler: Max depth reached', ['url' => $url, 'depth' => $depth]);
            return;
        }

        // Skip if already visited
        if (in_array($url, $this->visitedUrls)) {
            return;
        }

        // Mark as visited
        $this->visitedUrls[] = $url;

        Log::channel('webscraper')->info('IntelligentCrawler: Visiting page', [
            'url' => $url,
            'depth' => $depth,
        ]);

        // Scrape the page
        $scrapedData = $this->scraper->scrape($url);

        if (isset($scrapedData['error'])) {
            Log::channel('webscraper')->warning('IntelligentCrawler: Error scraping page', [
                'url' => $url,
                'error' => $scrapedData['error'],
            ]);
            return;
        }

        // Step 1: Check if current page content contains what we're looking for
        $contentMatch = $this->checkContentMatch($scrapedData, $query);
        if ($contentMatch) {
            Log::channel('webscraper')->info('IntelligentCrawler: Found match in content', ['url' => $url]);
            $this->foundResults[] = [
                'url' => $url,
                'title' => $scrapedData['metadata']['title'] ?? 'Untitled',
                'match_type' => 'content',
                'content_excerpt' => substr($scrapedData['content']['main'], 0, 500),
                'depth' => $depth,
            ];
        }

        // Step 2: Extract menu items from current page
        $html = $this->fetchRawHtml($url);
        $menuItems = $this->parser->extractMenuItems($html);

        if (empty($menuItems)) {
            Log::channel('webscraper')->debug('IntelligentCrawler: No menu items found', ['url' => $url]);
            return;
        }

        Log::channel('webscraper')->info('IntelligentCrawler: Found menu items', [
            'url' => $url,
            'count' => count($menuItems),
        ]);

        // Step 3: Search for query keywords in menu labels (PHP-level search)
        $matchingMenuItems = $this->searchInMenu($menuItems, $query);

        if (!empty($matchingMenuItems)) {
            Log::channel('webscraper')->info('IntelligentCrawler: Found matching menu items (PHP)', [
                'url' => $url,
                'matches' => count($matchingMenuItems),
            ]);

            // Follow matched menu links
            foreach ($matchingMenuItems as $item) {
                $normalizedUrl = $this->scraper->normalizeUrl($item['url'], $url);
                if ($normalizedUrl && !in_array($normalizedUrl, $this->visitedUrls)) {
                    $this->searchRecursive($normalizedUrl, $query, $depth + 1, $maxDepth);
                }
            }
        } else {
            // Step 4: No PHP match - ask AI to select relevant menu items
            Log::channel('webscraper')->info('IntelligentCrawler: No PHP match, asking AI', ['url' => $url]);
            $aiSelectedItems = $this->askAiToSelectMenu($menuItems, $query);

            if (!empty($aiSelectedItems)) {
                Log::channel('webscraper')->info('IntelligentCrawler: AI selected menu items', [
                    'url' => $url,
                    'selections' => count($aiSelectedItems),
                ]);

                // Follow AI-selected links
                foreach ($aiSelectedItems as $item) {
                    $normalizedUrl = $this->scraper->normalizeUrl($item['url'], $url);
                    if ($normalizedUrl && !in_array($normalizedUrl, $this->visitedUrls)) {
                        $this->searchRecursive($normalizedUrl, $query, $depth + 1, $maxDepth);
                    }
                }
            } else {
                // Step 5: AI found nothing relevant - scrape all menu pages
                Log::channel('webscraper')->info('IntelligentCrawler: AI found nothing, crawling all menu items', [
                    'url' => $url,
                    'items_to_crawl' => count($menuItems),
                ]);

                foreach (array_slice($menuItems, 0, 5) as $item) { // Limit to 5 to avoid explosion
                    $normalizedUrl = $this->scraper->normalizeUrl($item['url'], $url);
                    if ($normalizedUrl && !in_array($normalizedUrl, $this->visitedUrls)) {
                        $this->searchRecursive($normalizedUrl, $query, $depth + 1, $maxDepth);
                    }
                }
            }
        }
    }

    /**
     * Check if page content matches the search query
     * Searches in FULL content (includes header, footer, nav, everything)
     */
    protected function checkContentMatch(array $scrapedData, string $query): bool
    {
        // Use full content for keyword search (includes everything)
        $fullContent = strtolower($scrapedData['content']['full'] ?? '');

        $queryKeywords = $this->extractKeywords($query);

        foreach ($queryKeywords as $keyword) {
            if (stripos($fullContent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Search for query keywords in menu items (PHP-level)
     */
    protected function searchInMenu(array $menuItems, string $query): array
    {
        $matches = [];
        $queryKeywords = $this->extractKeywords($query);

        foreach ($menuItems as $item) {
            $label = strtolower($item['label']);
            $url = strtolower($item['url']);

            foreach ($queryKeywords as $keyword) {
                if (stripos($label, $keyword) !== false || stripos($url, $keyword) !== false) {
                    $matches[] = $item;
                    break; // Match found, add item once
                }
            }
        }

        return $matches;
    }

    /**
     * Ask AI to select relevant menu items based on query
     */
    protected function askAiToSelectMenu(array $menuItems, string $query): array
    {
        try {
            // Build menu text for AI
            $menuText = '';
            foreach ($menuItems as $index => $item) {
                $menuText .= ($index + 1) . ". {$item['label']} ({$item['url']})\n";
            }

            $systemPrompt = <<<PROMPT
Sei un assistente che analizza i menu dei siti web.
Data una lista di voci di menu e una query di ricerca, seleziona le voci di menu più rilevanti per trovare l'informazione cercata.

Rispondi SOLO con i numeri delle voci selezionate, separati da virgola (es: "1,3,5").
Se nessuna voce è rilevante, rispondi con "NONE".
PROMPT;

            $userPrompt = <<<PROMPT
MENU:
{$menuText}

QUERY: {$query}

Quali voci del menu dovrei visitare per trovare informazioni su "{$query}"?
Rispondi solo con i numeri separati da virgola, o "NONE" se nessuna voce è rilevante.
PROMPT;

            $apiKey = config('openapi.key');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini', // Use mini for speed
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 100,
                'temperature' => 0.3,
            ]);

            if (!$response->successful()) {
                Log::channel('webscraper')->error('IntelligentCrawler: AI selection failed', ['error' => $response->body()]);
                return [];
            }

            $result = $response->json();
            $aiAnswer = trim($result['choices'][0]['message']['content'] ?? '');

            Log::channel('webscraper')->info('IntelligentCrawler: AI response', ['answer' => $aiAnswer]);

            if ($aiAnswer === 'NONE' || empty($aiAnswer)) {
                return [];
            }

            // Parse AI response (e.g., "1,3,5")
            $selectedIndexes = array_map('trim', explode(',', $aiAnswer));
            $selectedItems = [];

            foreach ($selectedIndexes as $index) {
                if (is_numeric($index)) {
                    $numIndex = (int)$index - 1; // Convert to 0-based
                    if (isset($menuItems[$numIndex])) {
                        $selectedItems[] = $menuItems[$numIndex];
                    }
                }
            }

            return $selectedItems;

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('IntelligentCrawler: AI selection error', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Extract keywords from query
     */
    protected function extractKeywords(string $query): array
    {
        // Remove common Italian stop words
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

    /**
     * Fetch raw HTML (bypassing cache if needed)
     */
    protected function fetchRawHtml(string $url): string
    {
        try {
            $scrapedData = $this->scraper->scrape($url);
            // For menu extraction, we need to fetch again without boilerplate removal
            // This is a simplification - in production you might want to store raw HTML
            return $scrapedData['content']['main'] ?? '';
        } catch (\Exception $e) {
            Log::channel('webscraper')->error('IntelligentCrawler: Failed to fetch raw HTML', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }
}