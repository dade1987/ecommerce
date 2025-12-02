<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WebScraper SQLite Database Connection
    |--------------------------------------------------------------------------
    |
    | This is the database connection used by the WebScraper module to store
    | scraped page data. It uses SQLite for fast, file-based caching.
    |
    */

    'connection' => 'webscraper',

    'connections' => [
        'webscraper' => [
            'driver' => 'sqlite',
            'database' => storage_path('webscraper/webscraper.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | How long should scraped data be cached before it's considered stale
    | and needs to be re-scraped. Time is in seconds (86400 = 24 hours).
    |
    */

    'cache_ttl' => env('WEBSCRAPER_CACHE_TTL', 86400), // 24 hours
];