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
    | RAG (Retrieval-Augmented Generation) Configuration
    |--------------------------------------------------------------------------
    */
    'rag' => [
        // OpenAI Embedding Model
        'embedding' => [
            'model' => env('WEBSCRAPER_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'dimensions' => env('WEBSCRAPER_EMBEDDING_DIMENSIONS', 1536),
        ],

        // LLM for Q&A Generation
        'llm' => [
            'model' => env('WEBSCRAPER_LLM_MODEL', 'gpt-3.5-turbo'),
            'temperature' => env('WEBSCRAPER_LLM_TEMPERATURE', 0.7),
            'max_tokens' => env('WEBSCRAPER_LLM_MAX_TOKENS', 1000),
        ],

        // Vector Search Configuration
        'vector_search' => [
            'index_name' => env('WEBSCRAPER_VECTOR_INDEX_NAME', 'vector_index_1'),
            'top_k' => env('WEBSCRAPER_VECTOR_TOP_K', 10),
            'num_candidates' => env('WEBSCRAPER_VECTOR_NUM_CANDIDATES', 100),
            'min_similarity' => env('WEBSCRAPER_VECTOR_MIN_SIMILARITY', 0.7),
        ],

        // Text Search Configuration
        'text_search' => [
            'index_name' => env('WEBSCRAPER_TEXT_INDEX_NAME', 'text_search_index'),
            'fuzzy' => [
                'enabled' => env('WEBSCRAPER_TEXT_FUZZY_ENABLED', true),
                'max_edits' => env('WEBSCRAPER_TEXT_FUZZY_MAX_EDITS', 2),
                'prefix_length' => env('WEBSCRAPER_TEXT_FUZZY_PREFIX_LENGTH', 3),
            ],
            'autocomplete' => [
                'max_edits' => env('WEBSCRAPER_AUTOCOMPLETE_MAX_EDITS', 1),
                'prefix_length' => env('WEBSCRAPER_AUTOCOMPLETE_PREFIX_LENGTH', 2),
            ],
        ],

        // Hybrid Search Configuration (RRF)
        'hybrid' => [
            'rrf_k' => env('WEBSCRAPER_HYBRID_RRF_K', 60),
            'vector_weight' => env('WEBSCRAPER_HYBRID_VECTOR_WEIGHT', 1.0),
            'text_weight' => env('WEBSCRAPER_HYBRID_TEXT_WEIGHT', 1.0),
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
];