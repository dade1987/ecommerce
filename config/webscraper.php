<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebScraper Module Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => env('WEBSCRAPER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('WEBSCRAPER_CACHE_ENABLED', true),
        'ttl' => env('WEBSCRAPER_CACHE_TTL', 86400), // 24 hours in seconds
        'driver' => env('WEBSCRAPER_CACHE_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scraping Configuration
    |--------------------------------------------------------------------------
    */
    'scraping' => [
        // Maximum number of pages to crawl per site
        'max_pages' => env('WEBSCRAPER_MAX_PAGES', 10),

        // Request timeout in seconds
        'timeout' => env('WEBSCRAPER_TIMEOUT', 60),

        // User agent rotation - random user agent will be selected for each request
        'user_agents' => [
            // Chrome on Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',

            // Chrome on macOS
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',

            // Firefox on Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0',

            // Firefox on macOS
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:122.0) Gecko/20100101 Firefox/122.0',

            // Safari on macOS
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',

            // Edge on Windows
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0',
        ],

        // Fallback user agent (used if user_agents array is empty)
        'user_agent' => env('WEBSCRAPER_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),

        // Follow redirects
        'follow_redirects' => true,

        // Maximum redirects to follow
        'max_redirects' => 5,

        // Delay between requests (milliseconds)
        'delay' => env('WEBSCRAPER_DELAY', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Parsing Configuration
    |--------------------------------------------------------------------------
    */
    'parsing' => [
        // Maximum content length to process (characters)
        'max_content_length' => env('WEBSCRAPER_MAX_CONTENT_LENGTH', 50000),

        // Remove HTML tags
        'strip_tags' => false,

        // Preserve semantic structure
        'preserve_structure' => true,

        // Extract metadata
        'extract_metadata' => true,

        // Remove boilerplate
        'remove_boilerplate' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Analysis Configuration
    |--------------------------------------------------------------------------
    */
    'ai' => [
        'enabled' => env('WEBSCRAPER_AI_ENABLED', true),
        'model' => env('WEBSCRAPER_AI_MODEL', 'gpt-4o'),
        'temperature' => env('WEBSCRAPER_AI_TEMPERATURE', 0.7),
        'max_tokens' => env('WEBSCRAPER_AI_MAX_TOKENS', 2000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Scraping Configuration (Puppeteer)
    |--------------------------------------------------------------------------
    */
    'browser_scraping' => [
        // Enable headless browser for anti-bot protected sites
        'enabled' => env('WEBSCRAPER_BROWSER_ENABLED', true),

        // Timeout for browser scraping in seconds
        'timeout' => env('WEBSCRAPER_BROWSER_TIMEOUT', 120),

        // Domains that should always use browser scraping
        'force_domains' => [
            'amazon.',
            'ebay.',
            'walmart.',
            'target.',
            'bestbuy.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Domains
    |--------------------------------------------------------------------------
    | Leave empty to allow all domains
    */
    'allowed_domains' => [],

    /*
    |--------------------------------------------------------------------------
    | Blocked Domains
    |--------------------------------------------------------------------------
    */
    'blocked_domains' => [
        'localhost',
        '127.0.0.1',
        '0.0.0.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Result Cache Configuration
    |--------------------------------------------------------------------------
    | Cache for intelligent search results to avoid re-crawling
    */
    'search_cache' => [
        // Enable search result caching
        'enabled' => env('WEBSCRAPER_SEARCH_CACHE_ENABLED', true),

        // TTL for search results (7 days default)
        'ttl' => env('WEBSCRAPER_SEARCH_CACHE_TTL', 604800),

        // Auto-clean expired entries
        'auto_clean' => env('WEBSCRAPER_SEARCH_CACHE_AUTO_CLEAN', true),

        // Similarity threshold for fuzzy matching (0-100)
        // Queries with similarity >= this value will match cached results
        // Higher threshold = more strict matching, fewer false positives
        'similarity_threshold' => env('WEBSCRAPER_SEARCH_CACHE_SIMILARITY_THRESHOLD', 85),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    | SQLite database for storing scraped data and cache
    */
    'database' => [
        // Path to SQLite database
        'path' => env('WEBSCRAPER_DB_PATH', database_path('webscraper.sqlite')),

        // Cache TTL for scraped pages (24 hours default)
        'cache_ttl' => env('WEBSCRAPER_DB_CACHE_TTL', 86400),
    ],
];
