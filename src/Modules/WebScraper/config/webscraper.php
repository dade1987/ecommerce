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
        'timeout' => env('WEBSCRAPER_TIMEOUT', 30),

        // User agent for requests
        'user_agent' => env('WEBSCRAPER_USER_AGENT', 'Mozilla/5.0 (compatible; WebScraper/1.0)'),

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
];