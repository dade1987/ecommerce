<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Modules\WebScraper\Services\SearchStrategies\SearchStrategySelector;
use Modules\WebScraper\Services\SearchStrategies\SearchStrategyInterface;

class IntelligentCrawlerService
{
    protected WebScraperService $scraper;
    protected HtmlParserService $parser;
    protected AiAnalyzerService $aiAnalyzer;
    protected SitemapService $sitemapService;
    protected SearchFormService $searchFormService;
    protected SearchStrategySelector $strategySelector;
    protected array $visitedUrls = [];
    protected array $foundResults = [];
    protected string $searchMode = 'strict'; // 'strict' or 'related'
    protected ?string $baseDomain = null;
    protected bool $criticalErrorDetected = false; // Track critical errors that prevent scraping

    public function __construct(
        WebScraperService $scraper,
        HtmlParserService $parser,
        AiAnalyzerService $aiAnalyzer,
        SitemapService $sitemapService,
        SearchFormService $searchFormService,
        SearchStrategySelector $strategySelector
    ) {
        $this->scraper = $scraper;
        $this->parser = $parser;
        $this->aiAnalyzer = $aiAnalyzer;
        $this->sitemapService = $sitemapService;
        $this->searchFormService = $searchFormService;
        $this->strategySelector = $strategySelector;
    }

    /**
     * Intelligent search guided by website menu structure
     * Now includes sitemap and search form strategies!
     * Uses Strategy Pattern to optimize based on query type
     *
     * @param string $startUrl Starting URL to search from
     * @param string $query Search query
     * @param int $maxDepth Maximum depth for recursive crawling
     * @param string $mode Search mode: 'strict' (same domain only) or 'related' (follow external links)
     */
    public function intelligentSearch(string $startUrl, string $query, int $maxDepth = 3, string $mode = 'strict'): array
    {
        // Select appropriate strategy based on query
        $strategy = $this->strategySelector->selectStrategy($query);

        // Get strategy-specific configuration
        $maxPages = $strategy->getMaxPages();
        $maxDepth = $strategy->getMaxDepth();

        // Store mode for use in URL filtering
        $this->searchMode = $mode;
        $this->baseDomain = parse_url($startUrl, PHP_URL_HOST);

        Log::channel('webscraper')->info('IntelligentCrawler: Starting intelligent search', [
            'start_url' => $startUrl,
            'query' => $query,
            'strategy' => $strategy->getName(),
            'max_depth' => $maxDepth,
            'max_pages' => $maxPages,
            'mode' => $mode,
            'base_domain' => $this->baseDomain,
        ]);

        $this->visitedUrls = [];
        $this->foundResults = [];
        $this->criticalErrorDetected = false;

        // Try priority URLs first (if strategy defines them)
        $priorityUrls = $strategy->getPriorityUrls($startUrl);

        // For WeatherSearchStrategy, add city-specific URLs
        if ($strategy instanceof \Modules\WebScraper\Services\SearchStrategies\WeatherSearchStrategy) {
            $city = $strategy->extractCityFromQuery($query);
            if ($city) {
                Log::channel('webscraper')->info('IntelligentCrawler: WeatherStrategy detected city', [
                    'city' => $city,
                    'query' => $query,
                ]);
                $cityUrls = $strategy->getCityUrls($startUrl, $city);
                // Prepend city URLs to priority list (highest priority)
                $priorityUrls = array_merge($cityUrls, $priorityUrls);
                Log::channel('webscraper')->info('IntelligentCrawler: Added city-specific URLs', [
                    'city_urls_count' => count($cityUrls),
                ]);
            }
        }

        if (!empty($priorityUrls)) {
            Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 0 - Checking priority URLs', [
                'strategy' => $strategy->getName(),
                'urls_count' => count($priorityUrls),
            ]);

            foreach ($priorityUrls as $priorityUrl) {
                if (!in_array($priorityUrl, $this->visitedUrls)) {
                    $this->scrapeSinglePage($priorityUrl, $query, 0, 'priority_' . strtolower($strategy->getName()));
                }

                // Early stop if strategy allows it and we have enough results
                if ($strategy->shouldStopEarly() && count($this->foundResults) >= $strategy->getMinResultsForEarlyStop()) {
                    Log::channel('webscraper')->info('IntelligentCrawler: Early stop triggered', [
                        'strategy' => $strategy->getName(),
                        'results_found' => count($this->foundResults),
                    ]);

                    // Skip to AI ranking
                    if (!empty($this->foundResults)) {
                        $this->foundResults = $this->rankResultsWithAI($this->foundResults, $query);
                    }

                    return [
                        'query' => $query,
                        'pages_visited' => count($this->visitedUrls),
                        'results' => $this->foundResults,
                    ];
                }
            }
        }

        // STRATEGY 1: Try search form submission (fastest and most accurate!)
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 1 - Trying search form submission');
        $searchFormUrls = $this->searchFormService->submitSearchForm($startUrl, $query);

        if (!empty($searchFormUrls)) {
            Log::channel('webscraper')->info('IntelligentCrawler: Search form found results!', [
                'urls_found' => count($searchFormUrls),
            ]);

            // Scrape the search result pages (use strategy max_pages)
            foreach (array_slice($searchFormUrls, 0, $maxPages) as $resultUrl) {
                if (!in_array($resultUrl, $this->visitedUrls)) {
                    $this->scrapeSinglePage($resultUrl, $query, 0, 'search_form');
                }
            }
        }

        // Early exit if critical error detected (e.g., cURL encoding errors)
        if ($this->criticalErrorDetected) {
            Log::channel('webscraper')->warning('IntelligentCrawler: Critical error detected, skipping remaining strategies', [
                'start_url' => $startUrl,
            ]);
            return [
                'query' => $query,
                'pages_visited' => count($this->visitedUrls),
                'results' => $this->foundResults,
            ];
        }

        // STRATEGY 2: Check sitemap for relevant URLs (keyword-based)
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 2 - Searching in sitemap (keyword filter)');
        $sitemapUrls = $this->sitemapService->searchInSitemap($startUrl, $query);

        if (!empty($sitemapUrls)) {
            Log::channel('webscraper')->info('IntelligentCrawler: Sitemap found relevant URLs!', [
                'urls_found' => count($sitemapUrls),
            ]);

            // Scrape the sitemap URLs (use strategy max_pages)
            foreach (array_slice($sitemapUrls, 0, $maxPages) as $sitemapUrl) {
                if (!in_array($sitemapUrl, $this->visitedUrls)) {
                    $this->scrapeSinglePage($sitemapUrl, $query, 0, 'sitemap_keyword');
                }
            }
        }

        // Early exit if critical error detected
        if ($this->criticalErrorDetected) {
            Log::channel('webscraper')->warning('IntelligentCrawler: Critical error detected, skipping remaining strategies');
            return [
                'query' => $query,
                'pages_visited' => count($this->visitedUrls),
                'results' => $this->foundResults,
            ];
        }

        // STRATEGY 3: Menu-guided recursive crawling
        // The searchRecursive function will handle the homepage scraping AND menu extraction
        Log::channel('webscraper')->info('IntelligentCrawler: STRATEGY 3 - Menu-guided crawling');
        $this->searchRecursive($startUrl, $query, 0, $maxDepth);

        // Early exit if critical error detected
        if ($this->criticalErrorDetected) {
            Log::channel('webscraper')->warning('IntelligentCrawler: Critical error detected, skipping remaining strategies');
            return [
                'query' => $query,
                'pages_visited' => count($this->visitedUrls),
                'results' => $this->foundResults,
            ];
        }

        // STRATEGY 4 (FALLBACK): If we have less than 1 results, scrape more pages from sitemap
        $minResults = 1; // Minimum number of results before we're satisfied
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

        // AI Ranking: Analyze top candidates with AI for better relevance
        if (!empty($this->foundResults)) {
            Log::channel('webscraper')->info('IntelligentCrawler: Ranking results with AI', [
                'candidates' => count($this->foundResults),
            ]);
            $this->foundResults = $this->rankResultsWithAI($this->foundResults, $query);
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
     * Check if URL should be allowed based on search mode
     */
    protected function isUrlAllowed(string $url): bool
    {
        if ($this->searchMode === 'related') {
            return true; // Allow all URLs in related mode
        }

        // Strict mode: only allow same domain
        $urlDomain = parse_url($url, PHP_URL_HOST);
        $allowed = $urlDomain === $this->baseDomain;

        if (!$allowed) {
            Log::channel('webscraper')->debug('IntelligentCrawler: URL blocked by strict mode', [
                'url' => $url,
                'url_domain' => $urlDomain,
                'base_domain' => $this->baseDomain,
            ]);
        }

        return $allowed;
    }

    /**
     * Check if an error is critical and should stop further scraping attempts
     * Critical errors indicate that the site is not accessible (e.g., encoding issues, connection problems)
     */
    protected function isCriticalError(string $error): bool
    {
        $criticalPatterns = [
            'Unrecognized content encoding',
            'cURL error 61',
            'cURL error 6', // Couldn't resolve host
            'cURL error 7', // Failed to connect
            'cURL error 28', // Timeout
            'SSL certificate problem',
            'Connection refused',
            'Failed to fetch website',
        ];

        foreach ($criticalPatterns as $pattern) {
            if (stripos($error, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scrape a single page and check for query match
     */
    protected function scrapeSinglePage(string $url, string $query, int $depth, string $source): void
    {
        // Check if URL is allowed by search mode
        if (!$this->isUrlAllowed($url)) {
            return;
        }

        // Skip if already visited
        if (in_array($url, $this->visitedUrls)) {
            return;
        }

        $this->visitedUrls[] = $url;

        Log::channel('webscraper')->info('IntelligentCrawler: Scraping page', [
            'url' => $url,
            'source' => $source,
        ]);

        $scrapedData = $this->scraper->scrape($url, ['query' => $query]);

        if (isset($scrapedData['error'])) {
            Log::channel('webscraper')->warning('IntelligentCrawler: Error scraping page', [
                'url' => $url,
                'error' => $scrapedData['error'],
            ]);

            // Check if this is a critical error that prevents further scraping
            if ($this->isCriticalError($scrapedData['error'])) {
                $this->criticalErrorDetected = true;
                Log::channel('webscraper')->error('IntelligentCrawler: Critical error detected, will skip remaining strategies', [
                    'url' => $url,
                    'error' => $scrapedData['error'],
                ]);
            }

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
                'content_excerpt' => $this->extractHeaderAndFooter($scrapedData['content']['main']),
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
        $scrapedData = $this->scraper->scrape($url, ['query' => $query]);

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
                'content_excerpt' => $this->extractHeaderAndFooter($scrapedData['content']['main']),
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
     * Uses synonym expansion to improve match accuracy
     */
    protected function checkContentMatch(array $scrapedData, string $query): bool
    {
        // Use full content for keyword search (includes everything)
        $fullContent = strtolower($scrapedData['content']['full'] ?? '');

        // Extract keywords and expand with synonyms
        $queryKeywords = $this->extractKeywords($query);
        $expandedKeywords = $this->expandKeywordsWithSynonyms($queryKeywords);

        foreach ($expandedKeywords as $keyword) {
            if (stripos($fullContent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Italian synonym dictionary for common business terms
     */
    protected function getItalianSynonyms(): array
    {
        return [
            // Services / Solutions
            'servizi' => ['servizio', 'service', 'soluzioni', 'soluzione', 'solution', 'attività', 'attivita', 'prestazioni', 'prestazione', 'offerte', 'offerta', 'cosa facciamo', 'facciamo'],
            'soluzioni' => ['soluzione', 'solution', 'servizi', 'servizio', 'service', 'attività', 'attivita', 'prestazioni'],

            // Products
            'prodotti' => ['prodotto', 'product', 'products', 'articoli', 'articolo', 'items', 'catalogo', 'gamma'],
            'catalogo' => ['prodotti', 'prodotto', 'products', 'articoli', 'gamma', 'listino'],

            // Contacts
            'contatti' => ['contatto', 'contact', 'contacts', 'indirizzo', 'telefono', 'tel', 'email', 'mail', 'dove siamo', 'dove', 'scrivici', 'chiamaci', 'ubicazione', 'location', 'sede', 'recapiti', 'p.iva', 'piva', 'partita iva'],
            'indirizzo' => ['contatti', 'dove siamo', 'dove', 'ubicazione', 'location', 'sede', 'dove trovarci', 'via'],
            'telefono' => ['tel', 'phone', 'contatti', 'chiamaci', 'call', 'numero'],
            'partita iva' => ['p.iva', 'piva', 'partita-iva', 'iva', 'vat', 'contatti'],

            // About
            'chi siamo' => ['chi', 'about', 'azienda', 'storia', 'mission', 'valori', 'team', 'noi', 'profilo'],
            'azienda' => ['chi siamo', 'about', 'storia', 'profilo', 'chi', 'la nostra azienda'],

            // Portfolio / Projects / Realizations
            'portfolio' => ['progetti', 'progetto', 'projects', 'lavori', 'realizzazioni', 'realizzazione', 'casi studio', 'case study'],
            'progetti' => ['progetto', 'portfolio', 'lavori', 'realizzazioni', 'projects', 'casi studio'],
            'realizzazioni' => ['realizzazione', 'progetti', 'portfolio', 'lavori', 'projects', 'opere'],

            // News / Blog
            'news' => ['notizie', 'novita', 'blog', 'articoli', 'aggiornamenti', 'comunicati'],
            'blog' => ['articoli', 'news', 'notizie', 'post', 'novita'],

            // Support / Help / Guide
            'supporto' => ['assistenza', 'aiuto', 'help', 'support', 'faq', 'guide', 'guida'],
            'guide' => ['guida', 'tutorial', 'istruzioni', 'manuali', 'how to', 'come fare'],
            'assistenza' => ['supporto', 'aiuto', 'help', 'support', 'servizio clienti'],

            // Work / Career
            'lavora con noi' => ['lavora', 'career', 'carriere', 'lavoro', 'opportunita', 'posizioni', 'candidature', 'assunzioni', 'recruiting'],
            'carriere' => ['lavora con noi', 'lavoro', 'opportunita', 'career', 'posizioni'],

            // Method / Approach
            'metodo' => ['approccio', 'metodologia', 'come lavoriamo', 'processo', 'procedura', 'sistema'],

            // Prices / Quotes
            'prezzi' => ['prezzo', 'tariffe', 'tariffa', 'costi', 'costo', 'preventivi', 'preventivo', 'quotazioni'],
            'preventivi' => ['preventivo', 'quote', 'quotazione', 'richiesta preventivo', 'prezzi'],
        ];
    }

    /**
     * Expand keywords with synonyms
     */
    protected function expandKeywordsWithSynonyms(array $keywords): array
    {
        $synonyms = $this->getItalianSynonyms();
        $expandedKeywords = [];

        foreach ($keywords as $keyword) {
            // Add the original keyword
            $expandedKeywords[] = $keyword;

            // Check if this keyword is in the synonym dictionary
            foreach ($synonyms as $mainWord => $variants) {
                // If keyword matches main word, add all variants
                if ($keyword === $mainWord) {
                    $expandedKeywords = array_merge($expandedKeywords, $variants);
                }
                // If keyword matches a variant, add main word and all variants
                else if (in_array($keyword, $variants)) {
                    $expandedKeywords[] = $mainWord;
                    $expandedKeywords = array_merge($expandedKeywords, $variants);
                }
            }
        }

        // Remove duplicates and return
        return array_unique($expandedKeywords);
    }

    /**
     * Search for query keywords in menu items (PHP-level with synonym support)
     */
    protected function searchInMenu(array $menuItems, string $query): array
    {
        $matches = [];
        $queryKeywords = $this->extractKeywords($query);

        // Expand keywords with Italian synonyms
        $expandedKeywords = $this->expandKeywordsWithSynonyms($queryKeywords);

        Log::channel('webscraper')->info('IntelligentCrawler: Keyword expansion', [
            'original' => $queryKeywords,
            'expanded' => $expandedKeywords,
        ]);

        foreach ($menuItems as $item) {
            $label = strtolower($item['label']);
            $url = strtolower($item['url']);

            foreach ($expandedKeywords as $keyword) {
                if (stripos($label, $keyword) !== false || stripos($url, $keyword) !== false) {
                    $matches[] = $item;
                    Log::channel('webscraper')->info('IntelligentCrawler: Menu match found', [
                        'keyword' => $keyword,
                        'menu_label' => $item['label'],
                    ]);
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
     * Fetch raw HTML (for menu extraction)
     */
    protected function fetchRawHtml(string $url): string
    {
        try {
            $scrapedData = $this->scraper->scrape($url);
            // Use raw_html if available (contains the full HTML with nav/menu)
            return $scrapedData['raw_html'] ?? '';
        } catch (\Exception $e) {
            Log::channel('webscraper')->error('IntelligentCrawler: Failed to fetch raw HTML', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return '';
        }
    }

    /**
     * Rank results with AI for better relevance
     * Uses a single AI call to score all candidates efficiently
     *
     * @param array $candidates Array of candidate results from keyword matching
     * @param string $query User's search query
     * @return array Filtered and sorted results (only relevant ones)
     */
    protected function rankResultsWithAI(array $candidates, string $query): array
    {
        try {
            // Limit to top 10 candidates to avoid huge prompts
            $candidatesToAnalyze = array_slice($candidates, 0, 10);

            // Build candidates text for AI
            $candidatesText = '';
            foreach ($candidatesToAnalyze as $index => $candidate) {
                $title = $candidate['title'] ?? 'Untitled';
                $excerpt = $candidate['content_excerpt'] ?? '';
                $url = $candidate['url'] ?? '';

                $candidatesText .= ($index + 1) . ". **{$title}**\n";
                $candidatesText .= "   URL: {$url}\n";
                $candidatesText .= "   Contenuto: {$excerpt}\n\n";
            }

            $systemPrompt = "Valuta rilevanza pagine web. Score 0-10. Rispondi: 1: X\n2: Y\n3: Z";

            $userPrompt = "Query: {$query}\n\n{$candidatesText}\nScore (0-10):";

            $apiKey = config('openapi.key');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo', // 3x faster, 10x cheaper than gpt-4o-mini
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 50, // Ridotto da 200 a 50 (formato semplice)
                'temperature' => 0.1, // Molto basso per risposte consistenti
            ]);

            if (!$response->successful()) {
                Log::channel('webscraper')->error('IntelligentCrawler: AI ranking failed', [
                    'error' => $response->body(),
                ]);
                // Return original results if AI fails
                return $candidates;
            }

            $result = $response->json();
            $aiAnswer = trim($result['choices'][0]['message']['content'] ?? '');

            Log::channel('webscraper')->info('IntelligentCrawler: AI ranking response', [
                'answer' => $aiAnswer,
                'tokens_used' => $result['usage']['total_tokens'] ?? 0,
            ]);

            if (empty($aiAnswer)) {
                return $candidates;
            }

            // Parse AI response
            // Format can be: "1: 9\n2: 3\n3: 7" OR just "8" (for single candidate)
            $scores = [];
            $lines = explode("\n", $aiAnswer);

            foreach ($lines as $line) {
                $line = trim($line);

                // Try format "1: 8"
                if (preg_match('/(\d+):\s*(\d+)/', $line, $matches)) {
                    $index = (int)$matches[1] - 1; // Convert to 0-based
                    $score = (int)$matches[2];

                    if (isset($candidatesToAnalyze[$index])) {
                        $scores[$index] = $score;
                    }
                }
                // Fallback: just a number (for single candidate)
                elseif (preg_match('/^(\d+)$/', $line, $matches)) {
                    $score = (int)$matches[1];
                    // Assign to first candidate
                    if (count($candidatesToAnalyze) === 1 && !isset($scores[0])) {
                        $scores[0] = $score;
                    }
                }
            }

            Log::channel('webscraper')->info('IntelligentCrawler: Parsed AI scores', [
                'scores' => $scores,
                'candidates_count' => count($candidatesToAnalyze),
            ]);

            // Filter: Keep only results with score >= 4 (at least partially relevant)
            $filteredResults = [];
            foreach ($candidatesToAnalyze as $index => $candidate) {
                $score = $scores[$index] ?? 0;

                if ($score >= 4) {
                    $candidate['relevance_score'] = $score;
                    $filteredResults[] = $candidate;
                }
            }

            // Sort by relevance score (descending)
            usort($filteredResults, function($a, $b) {
                return ($b['relevance_score'] ?? 0) <=> ($a['relevance_score'] ?? 0);
            });

            Log::channel('webscraper')->info('IntelligentCrawler: AI ranking completed', [
                'original_count' => count($candidates),
                'analyzed_count' => count($candidatesToAnalyze),
                'filtered_count' => count($filteredResults),
            ]);

            // If we filtered out everything, return original results (AI might be too strict)
            if (empty($filteredResults) && !empty($candidates)) {
                Log::channel('webscraper')->warning('IntelligentCrawler: AI filtered out all results, returning originals');
                return $candidates;
            }

            return $filteredResults;

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('IntelligentCrawler: AI ranking error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Return original results if anything fails
            return $candidates;
        }
    }

    /**
     * Extract header (first 500 chars) + footer (last 500 chars) for AI analysis
     * This ensures we capture both navigation content and footer contact info
     *
     * @param string $content Full page content
     * @return string Header + footer excerpt (max ~1000 chars)
     */
    protected function extractHeaderAndFooter(string $content): string
    {
        $contentLength = strlen($content);

        // If content is short (< 1000 chars), return as-is
        if ($contentLength <= 1000) {
            return $content;
        }

        // Extract first 500 and last 500 characters
        $header = substr($content, 0, 500);
        $footer = substr($content, -500);

        // Add separator to distinguish header from footer
        return $header . "\n\n[...]\n\n" . $footer;
    }
}