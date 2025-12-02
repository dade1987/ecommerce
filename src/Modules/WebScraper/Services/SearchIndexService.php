<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Traits\EnhancesSearchQueries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * SearchIndexService
 *
 * Service for MongoDB Atlas Search (standard text search, NOT vector search).
 * Provides full-text search capabilities with autocomplete, fuzzy matching, and faceting.
 *
 * Differences from Vector Search:
 * - Vector Search: Semantic similarity using embeddings (cosine similarity)
 * - Text Search: Keyword matching with linguistic analysis (stemming, synonyms)
 *
 * Use Cases:
 * - Autocomplete search bars
 * - Fuzzy matching for typos
 * - Faceted search (filtering by domain, categories)
 * - Hybrid search (combining vector + text search)
 */
class SearchIndexService
{
    use EnhancesSearchQueries;

    /**
     * Search index name
     */
    protected string $indexName;

    /**
     * Fuzzy search configuration
     */
    protected bool $fuzzyEnabled;
    protected int $fuzzyMaxEdits;
    protected int $fuzzyPrefixLength;

    /**
     * Autocomplete configuration
     */
    protected int $autocompleteMaxEdits;
    protected int $autocompletePrefixLength;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->indexName = config('webscraper.rag.text_search.index_name', 'text_search_index');

        // Load fuzzy search config
        $this->fuzzyEnabled = config('webscraper.rag.text_search.fuzzy.enabled', true);
        $this->fuzzyMaxEdits = config('webscraper.rag.text_search.fuzzy.max_edits', 2);
        $this->fuzzyPrefixLength = config('webscraper.rag.text_search.fuzzy.prefix_length', 3);

        // Load autocomplete config
        $this->autocompleteMaxEdits = config('webscraper.rag.text_search.autocomplete.max_edits', 1);
        $this->autocompletePrefixLength = config('webscraper.rag.text_search.autocomplete.prefix_length', 2);
    }

    /**
     * Create a text search index on MongoDB Atlas
     *
     * @param string $collection Collection name (e.g., 'webscraper_pages' or 'webscraper_chunks')
     * @param array $fields Fields to index (e.g., ['content', 'title'])
     * @param array $options Additional index options
     * @return array Result with status and message
     * @throws Exception
     */
    public function createIndex(
        string $collection,
        array $fields = ['content', 'title'],
        array $options = []
    ): array {
        Log::channel('webscraper')->info('SearchIndex: Creating text search index', [
            'collection' => $collection,
            'fields' => $fields,
            'index_name' => $this->indexName,
        ]);

        try {
            // Build field mappings for Atlas Search
            $mappings = [];
            foreach ($fields as $field) {
                $mappings[$field] = [
                    'type' => 'string',
                    'analyzer' => 'lucene.standard', // Standard analyzer with stemming
                ];
            }

            // Add autocomplete analyzer for specific fields
            if (in_array('title', $fields)) {
                $mappings['title'] = [
                    'type' => 'autocomplete',
                    'tokenization' => 'edgeGram',
                    'minGrams' => 2,
                    'maxGrams' => 15,
                    'foldDiacritics' => true,
                ];
            }

            // Index definition for Atlas Search
            $indexDefinition = [
                'name' => $this->indexName,
                'mappings' => [
                    'dynamic' => false,
                    'fields' => $mappings,
                ],
            ];

            // Add synonyms if provided
            if (isset($options['synonyms'])) {
                $indexDefinition['synonyms'] = $options['synonyms'];
            }

            Log::channel('webscraper')->info('SearchIndex: Index definition created', [
                'definition' => json_encode($indexDefinition, JSON_PRETTY_PRINT),
            ]);

            // Note: Index creation must be done via Atlas UI or Atlas CLI
            // This method returns the configuration that should be applied
            return [
                'status' => 'pending',
                'message' => 'Index configuration ready. Apply this in MongoDB Atlas Console.',
                'definition' => $indexDefinition,
                'instructions' => [
                    '1. Go to MongoDB Atlas → Database → Browse Collections',
                    '2. Select your database and collection: ' . $collection,
                    '3. Click on "Search Indexes" tab',
                    '4. Click "Create Search Index"',
                    '5. Choose "JSON Editor" and paste the definition above',
                    '6. Click "Create Search Index"',
                ],
            ];

        } catch (Exception $e) {
            Log::channel('webscraper')->error('SearchIndex: Failed to create index definition', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to create search index: ' . $e->getMessage());
        }
    }

    /**
     * Perform a text search on indexed collection
     *
     * @param string $collection Collection name
     * @param string $query Search query
     * @param array $options Search options (limit, fuzzy, autocomplete, etc.)
     * @return array Search results with scores
     * @throws Exception
     */
    public function search(
        string $collection,
        string $query,
        array $options = []
    ): array {
        Log::channel('webscraper')->info('SearchIndex: Performing text search', [
            'collection' => $collection,
            'query' => $query,
            'options' => $options,
        ]);

        try {
            // Extract options
            $limit = $options['limit'] ?? 10;
            $fuzzy = $options['fuzzy'] ?? false;
            $domain = $options['domain'] ?? null;

            // Enhance query using strategy pattern (if set)
            $enhancedQuery = $this->enhanceQuery($query, ['domain' => $domain]);

            // Build search stage with native domain pre-filtering (if domain specified)
            if ($domain) {
                // Support both www and non-www variants
                $domainVariants = [$domain];

                if (str_starts_with($domain, 'www.')) {
                    $domainVariants[] = substr($domain, 4);
                } else {
                    $domainVariants[] = 'www.' . $domain;
                }

                // Build compound query with native pre-filtering
                $textQuery = [
                    'query' => $enhancedQuery,
                    'path' => $options['fields'] ?? ['content', 'title'],
                ];

                // Add fuzzy matching for typo tolerance
                if ($fuzzy && $this->fuzzyEnabled) {
                    $textQuery['fuzzy'] = [
                        'maxEdits' => $this->fuzzyMaxEdits,
                        'prefixLength' => $this->fuzzyPrefixLength,
                    ];
                }

                $searchStage = [
                    'index' => $this->indexName,
                    'compound' => [
                        'must' => [
                            ['text' => $textQuery]
                        ],
                        'filter' => [
                            ['text' => [
                                'query' => $domainVariants,
                                'path' => 'domain'
                            ]]
                        ]
                    ]
                ];

                Log::channel('webscraper')->debug('SearchIndex: Domain pre-filter applied (Atlas native)', [
                    'domain' => $domain,
                    'variants' => $domainVariants,
                ]);

            } else {
                // No domain filter - simple text search
                $searchStage = [
                    'index' => $this->indexName,
                    'text' => [
                        'query' => $enhancedQuery,
                        'path' => $options['fields'] ?? ['content', 'title'],
                    ],
                ];

                // Add fuzzy matching for typo tolerance
                if ($fuzzy && $this->fuzzyEnabled) {
                    $searchStage['text']['fuzzy'] = [
                        'maxEdits' => $this->fuzzyMaxEdits,
                        'prefixLength' => $this->fuzzyPrefixLength,
                    ];
                }
            }

            // Build aggregation pipeline
            $pipeline = [
                ['$search' => $searchStage],
                ['$addFields' => [
                    'score' => ['$meta' => 'searchScore'],
                ]],
            ];

            // Limit results AFTER filtering
            $pipeline[] = ['$limit' => $limit];

            // Convert page_id from string to ObjectId for lookup
            $pipeline[] = [
                '$addFields' => [
                    'page_id_obj' => ['$toObjectId' => '$page_id']
                ]
            ];

            // Lookup page data to get URL and title
            $pipeline[] = [
                '$lookup' => [
                    'from' => 'webscraper_pages',
                    'localField' => 'page_id_obj',
                    'foreignField' => '_id',
                    'as' => 'page_data'
                ]
            ];

            // Unwind page_data (convert array to object)
            $pipeline[] = ['$unwind' => ['path' => '$page_data', 'preserveNullAndEmptyArrays' => true]];

            // Add URL and title from page
            $pipeline[] = [
                '$addFields' => [
                    'url' => '$page_data.url',
                    'title' => '$page_data.title',
                ]
            ];

            // Execute search
            $results = DB::connection('mongodb')
                ->getCollection($collection)
                ->aggregate($pipeline)
                ->toArray();

            Log::channel('webscraper')->info('SearchIndex: Search completed', [
                'results_count' => count($results),
                'query' => $query,
            ]);

            return [
                'query' => $query,
                'results' => $results,
                'count' => count($results),
                'method' => 'atlas_text_search',
            ];

        } catch (Exception $e) {
            Log::channel('webscraper')->error('SearchIndex: Search failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Text search failed: ' . $e->getMessage());
        }
    }

    /**
     * Autocomplete search for search-as-you-type functionality
     *
     * @param string $collection Collection name
     * @param string $query Partial query (e.g., "pizz" → suggests "pizza")
     * @param array $options Search options
     * @return array Autocomplete suggestions
     * @throws Exception
     */
    public function autocomplete(
        string $collection,
        string $query,
        array $options = []
    ): array {
        Log::channel('webscraper')->info('SearchIndex: Autocomplete search', [
            'collection' => $collection,
            'query' => $query,
        ]);

        try {
            $limit = $options['limit'] ?? 5;
            $field = $options['field'] ?? 'title';

            // Build autocomplete search stage
            $pipeline = [
                [
                    '$search' => [
                        'index' => $this->indexName,
                        'autocomplete' => [
                            'query' => $query,
                            'path' => $field,
                            'tokenOrder' => 'sequential', // Match in order
                            'fuzzy' => [
                                'maxEdits' => $this->autocompleteMaxEdits,
                                'prefixLength' => $this->autocompletePrefixLength,
                            ],
                        ],
                    ],
                ],
                ['$limit' => $limit],
                ['$project' => [
                    $field => 1,
                    'url' => 1,
                    'score' => ['$meta' => 'searchScore'],
                ]],
            ];

            $results = DB::connection('mongodb')
                ->getCollection($collection)
                ->aggregate($pipeline)
                ->toArray();

            Log::channel('webscraper')->info('SearchIndex: Autocomplete completed', [
                'suggestions_count' => count($results),
            ]);

            return [
                'query' => $query,
                'suggestions' => $results,
                'count' => count($results),
            ];

        } catch (Exception $e) {
            Log::channel('webscraper')->error('SearchIndex: Autocomplete failed', [
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Autocomplete search failed: ' . $e->getMessage());
        }
    }

    /**
     * Faceted search - get counts by category/domain
     *
     * @param string $collection Collection name
     * @param string $query Search query
     * @param array $facets Facet fields (e.g., ['domain'])
     * @return array Faceted results
     * @throws Exception
     */
    public function facetedSearch(
        string $collection,
        string $query,
        array $facets = ['domain']
    ): array {
        Log::channel('webscraper')->info('SearchIndex: Faceted search', [
            'collection' => $collection,
            'query' => $query,
            'facets' => $facets,
        ]);

        try {
            // Build facet definitions
            $facetDefinitions = [];
            foreach ($facets as $facet) {
                $facetDefinitions[$facet] = [
                    'type' => 'string',
                    'path' => $facet,
                    'numBuckets' => 10,
                ];
            }

            $pipeline = [
                [
                    '$searchMeta' => [
                        'index' => $this->indexName,
                        'facet' => [
                            'operator' => [
                                'text' => [
                                    'query' => $query,
                                    'path' => ['content', 'title'],
                                ],
                            ],
                            'facets' => $facetDefinitions,
                        ],
                    ],
                ],
            ];

            $results = DB::connection('mongodb')
                ->getCollection($collection)
                ->aggregate($pipeline)
                ->toArray();

            Log::channel('webscraper')->info('SearchIndex: Faceted search completed');

            return [
                'query' => $query,
                'facets' => $results[0] ?? [],
            ];

        } catch (Exception $e) {
            Log::channel('webscraper')->error('SearchIndex: Faceted search failed', [
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Faceted search failed: ' . $e->getMessage());
        }
    }

    /**
     * Set custom index name
     *
     * @param string $indexName Index name
     * @return self
     */
    public function setIndexName(string $indexName): self
    {
        $this->indexName = $indexName;
        return $this;
    }
}