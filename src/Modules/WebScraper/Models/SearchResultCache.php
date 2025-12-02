<?php

namespace Modules\WebScraper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SearchResultCache extends Model
{
    protected $connection = 'webscraper';
    protected $table = 'search_result_cache';
    public $timestamps = false;

    protected $fillable = [
        'site_url',
        'query_hash',
        'original_query',
        'results_json',
        'ai_analysis',
        'pages_visited',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];


    /**
     * Get cached search result if exists and not expired
     * Uses similarity matching to find semantically similar queries
     */
    public static function getCached(string $siteUrl, string $query): ?array
    {
        // Normalize URL to base domain for cache lookup consistency
        // This ensures that requests to https://example.com/product/123 and https://example.com
        // both use the same cache, since intelligent search starts from homepage anyway
        $normalizedSiteUrl = self::normalizeUrl($siteUrl);

        if ($normalizedSiteUrl !== $siteUrl) {
            Log::channel('webscraper')->debug('SearchResultCache: Normalized URL for cache lookup', [
                'original_url' => $siteUrl,
                'normalized_url' => $normalizedSiteUrl,
            ]);
        }

        $queryHash = self::hashQuery($query);
        $normalizedQuery = self::normalizeQuery($query);
        $now = now();

        // First try exact hash match
        $cached = self::where('site_url', $normalizedSiteUrl)
            ->where('query_hash', $queryHash)
            ->where('expires_at', '>', $now)
            ->first();

        if ($cached) {
            Log::channel('webscraper')->info('SearchResultCache: Cache HIT (exact)', [
                'site_url' => $siteUrl,
                'query' => $query,
                'cached_query' => $cached->original_query,
                'cached_at' => $cached->created_at,
            ]);

            return [
                'results' => json_decode($cached->results_json, true),
                'pages_visited' => $cached->pages_visited,
                'original_query' => $cached->original_query,
                'ai_analysis' => $cached->ai_analysis, // Original AI analysis for reference
                'cached_at' => $cached->created_at,
                'from_cache' => true,
            ];
        }

        // If no exact match, try keyword-based pattern matching first (faster, more reliable)
        $keywordCache = self::findCacheByKeywords($normalizedSiteUrl, $normalizedQuery, $now);

        if ($keywordCache) {
            Log::channel('webscraper')->info('SearchResultCache: Cache HIT (keyword match)', [
                'site_url' => $siteUrl,
                'query' => $query,
                'cached_query' => $keywordCache->original_query,
                'matched_keywords' => $keywordCache->matched_keywords,
                'cached_at' => $keywordCache->created_at,
            ]);

            return [
                'results' => json_decode($keywordCache->results_json, true),
                'pages_visited' => $keywordCache->pages_visited,
                'original_query' => $keywordCache->original_query,
                'ai_analysis' => $keywordCache->ai_analysis,
                'cached_at' => $keywordCache->created_at,
                'from_cache' => true,
            ];
        }

        // If no keyword match, fall back to similarity matching (use normalized URL)
        $similarCache = self::findSimilarCache($normalizedSiteUrl, $normalizedQuery, $now);

        if ($similarCache) {
            Log::channel('webscraper')->info('SearchResultCache: Cache HIT (similar)', [
                'site_url' => $siteUrl,
                'query' => $query,
                'cached_query' => $similarCache->original_query,
                'similarity' => $similarCache->similarity_score,
                'cached_at' => $similarCache->created_at,
            ]);

            return [
                'results' => json_decode($similarCache->results_json, true),
                'pages_visited' => $similarCache->pages_visited,
                'original_query' => $similarCache->original_query,
                'ai_analysis' => $similarCache->ai_analysis,
                'cached_at' => $similarCache->created_at,
                'from_cache' => true,
            ];
        }

        Log::channel('webscraper')->info('SearchResultCache: Cache MISS', [
            'site_url' => $siteUrl,
            'query' => $query,
        ]);

        return null;
    }

    /**
     * Find cached queries by keyword pattern matching (SQL-like LIKE %value%)
     * This is faster and more reliable than similarity calculations
     * Returns the first match found
     */
    protected static function findCacheByKeywords(string $siteUrl, string $normalizedQuery, $now): ?object
    {
        // Extract meaningful keywords from normalized query
        $keywords = explode(' ', $normalizedQuery);

        // Filter out very short words that won't be meaningful for pattern matching
        $keywords = array_filter($keywords, function($word) {
            return strlen($word) >= 4; // Only use words with 4+ chars for pattern matching
        });

        if (empty($keywords)) {
            Log::channel('webscraper')->debug('SearchResultCache: No keywords extracted for pattern matching', [
                'normalized_query' => $normalizedQuery,
            ]);
            return null;
        }

        Log::channel('webscraper')->debug('SearchResultCache: Starting keyword pattern matching', [
            'normalized_query' => $normalizedQuery,
            'keywords' => $keywords,
        ]);

        // Get all active cache entries for this site
        $candidates = self::where('site_url', $siteUrl)
            ->where('expires_at', '>', $now)
            ->get();

        if ($candidates->isEmpty()) {
            Log::channel('webscraper')->debug('SearchResultCache: No candidates found for keyword matching', [
                'site_url' => $siteUrl,
            ]);
            return null;
        }

        // Search for cached queries that contain ALL keywords (AND logic)
        foreach ($candidates as $candidate) {
            $cachedNormalized = self::normalizeQuery($candidate->original_query);
            $matchedKeywords = [];
            $allKeywordsMatch = true;

            // Check if ALL keywords are present in cached query
            foreach ($keywords as $keyword) {
                // Use stripos for case-insensitive substring search (SQL LIKE %value%)
                if (stripos($cachedNormalized, $keyword) !== false) {
                    $matchedKeywords[] = $keyword;
                } else {
                    $allKeywordsMatch = false;
                    break;
                }
            }

            if ($allKeywordsMatch && count($matchedKeywords) > 0) {
                Log::channel('webscraper')->info('SearchResultCache: Keyword match found', [
                    'original_query' => $candidate->original_query,
                    'cached_normalized' => $cachedNormalized,
                    'query_normalized' => $normalizedQuery,
                    'matched_keywords' => $matchedKeywords,
                ]);

                $candidate->matched_keywords = implode(', ', $matchedKeywords);
                return $candidate;
            }

            Log::channel('webscraper')->debug('SearchResultCache: Keyword match failed', [
                'original_query' => $candidate->original_query,
                'cached_normalized' => $cachedNormalized,
                'matched_keywords' => $matchedKeywords,
                'all_matched' => $allKeywordsMatch,
            ]);
        }

        // If no match with ALL keywords, try with ANY keyword (OR logic) for broader matching
        Log::channel('webscraper')->debug('SearchResultCache: No match with ALL keywords, trying ANY keyword');

        foreach ($candidates as $candidate) {
            $cachedNormalized = self::normalizeQuery($candidate->original_query);
            $matchedKeywords = [];

            // Check if ANY keyword is present in cached query
            foreach ($keywords as $keyword) {
                if (stripos($cachedNormalized, $keyword) !== false) {
                    $matchedKeywords[] = $keyword;
                }
            }

            // Require at least 50% of keywords to match for broader matching
            $matchPercentage = count($matchedKeywords) / count($keywords) * 100;

            if ($matchPercentage >= 50) {
                Log::channel('webscraper')->info('SearchResultCache: Partial keyword match found', [
                    'original_query' => $candidate->original_query,
                    'cached_normalized' => $cachedNormalized,
                    'query_normalized' => $normalizedQuery,
                    'matched_keywords' => $matchedKeywords,
                    'match_percentage' => round($matchPercentage, 1),
                ]);

                $candidate->matched_keywords = implode(', ', $matchedKeywords);
                return $candidate;
            }
        }

        Log::channel('webscraper')->debug('SearchResultCache: No keyword matches found');
        return null;
    }

    /**
     * Find similar cached queries using Levenshtein distance and semantic similarity
     */
    protected static function findSimilarCache(string $siteUrl, string $normalizedQuery, $now): ?object
    {
        // Get all active cache entries for this site
        $candidates = self::where('site_url', $siteUrl)
            ->where('expires_at', '>', $now)
            ->get();

        if ($candidates->isEmpty()) {
            Log::channel('webscraper')->debug('SearchResultCache: No candidates found for similarity matching', [
                'site_url' => $siteUrl,
            ]);
            return null;
        }

        $bestMatch = null;
        $bestSimilarity = 0;
        $threshold = config('webscraper.search_cache.similarity_threshold', 85); // Higher threshold to avoid false positives

        Log::channel('webscraper')->debug('SearchResultCache: Starting similarity matching', [
            'normalized_query' => $normalizedQuery,
            'candidates_count' => $candidates->count(),
            'threshold' => $threshold,
        ]);

        foreach ($candidates as $candidate) {
            $cachedNormalized = self::normalizeQuery($candidate->original_query);

            // Skip if one query is significantly longer than the other (indicates different specificity)
            $queryWords = explode(' ', $normalizedQuery);
            $cachedWords = explode(' ', $cachedNormalized);
            $wordCountDiff = abs(count($queryWords) - count($cachedWords));

            Log::channel('webscraper')->debug('SearchResultCache: Comparing queries', [
                'original_query' => $candidate->original_query,
                'cached_normalized' => $cachedNormalized,
                'query_normalized' => $normalizedQuery,
                'query_words' => count($queryWords),
                'cached_words' => count($cachedWords),
                'word_count_diff' => $wordCountDiff,
            ]);

            // If word count difference is > 2, likely different queries (e.g., "servizio" vs "infissi servizio")
            if ($wordCountDiff > 2) {
                Log::channel('webscraper')->debug('SearchResultCache: Skipped - word count difference too large', [
                    'original_query' => $candidate->original_query,
                    'word_count_diff' => $wordCountDiff,
                ]);
                continue;
            }

            // Calculate similarity using similar_text
            $similarity = 0;
            similar_text($normalizedQuery, $cachedNormalized, $similarity);

            // Also calculate Levenshtein distance for short queries
            if (strlen($normalizedQuery) < 50 && strlen($cachedNormalized) < 50) {
                $maxLen = max(strlen($normalizedQuery), strlen($cachedNormalized));
                if ($maxLen > 0) {
                    $levenshtein = levenshtein($normalizedQuery, $cachedNormalized);
                    $levenshteinSimilarity = (1 - ($levenshtein / $maxLen)) * 100;

                    Log::channel('webscraper')->debug('SearchResultCache: Similarity calculations', [
                        'original_query' => $candidate->original_query,
                        'similar_text_score' => round($similarity, 2),
                        'levenshtein_distance' => $levenshtein,
                        'levenshtein_similarity' => round($levenshteinSimilarity, 2),
                        'combined_similarity' => round(($similarity + $levenshteinSimilarity) / 2, 2),
                    ]);

                    // Use average of both metrics
                    $similarity = ($similarity + $levenshteinSimilarity) / 2;
                }
            } else {
                Log::channel('webscraper')->debug('SearchResultCache: Only similar_text used', [
                    'original_query' => $candidate->original_query,
                    'similar_text_score' => round($similarity, 2),
                ]);
            }

            if ($similarity > $bestSimilarity && $similarity >= $threshold) {
                $bestSimilarity = $similarity;
                $bestMatch = $candidate;
                $bestMatch->similarity_score = round($similarity, 2);

                Log::channel('webscraper')->debug('SearchResultCache: New best match found', [
                    'original_query' => $candidate->original_query,
                    'similarity' => round($similarity, 2),
                ]);
            } else {
                Log::channel('webscraper')->debug('SearchResultCache: Not a match', [
                    'original_query' => $candidate->original_query,
                    'similarity' => round($similarity, 2),
                    'threshold' => $threshold,
                    'reason' => $similarity < $threshold ? 'below_threshold' : 'not_best',
                ]);
            }
        }

        if ($bestMatch) {
            Log::channel('webscraper')->info('SearchResultCache: Best match selected', [
                'original_query' => $bestMatch->original_query,
                'similarity' => $bestMatch->similarity_score,
            ]);
        } else {
            Log::channel('webscraper')->info('SearchResultCache: No suitable match found', [
                'best_similarity' => round($bestSimilarity, 2),
                'threshold' => $threshold,
            ]);
        }

        return $bestMatch;
    }

    /**
     * Normalize query for similarity comparison
     * Returns a string with only core keywords
     */
    protected static function normalizeQuery(string $query): string
    {
        // Lowercase and trim
        $normalized = trim(strtolower($query));

        // Remove punctuation
        $normalized = preg_replace('/[^\w\s]/u', ' ', $normalized);

        // Remove extra spaces
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // Aggressive stop words removal - including action verbs that don't add semantic value
        $stopWords = [
            // Articles
            'il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una',
            // Prepositions and contractions
            'di', 'a', 'da', 'in', 'con', 'su', 'per', 'tra', 'fra',
            'del', 'dello', 'della', 'dei', 'degli', 'delle',
            'al', 'allo', 'alla', 'ai', 'agli', 'alle',
            'dal', 'dallo', 'dalla', 'dai', 'dagli', 'dalle',
            'nel', 'nello', 'nella', 'nei', 'negli', 'nelle',
            'sul', 'sullo', 'sulla', 'sui', 'sugli', 'sulle',
            // Common function words
            'che', 'dell', 'dall', 'all', 'qual', 'questa', 'questo', 'tutti', 'tutte', 'tutto',
            // Common verbs that don't add semantic value
            'sono', 'è', 'ha', 'hanno', 'essere', 'avere',
            'offre', 'offrono', 'offerte', 'offerti', 'offerta', 'offerto',
            // Action verbs that don't change query intent
            'trova', 'cerca', 'cercare', 'trovare', 'mostra', 'vedere', 'visualizza',
            'dimmi', 'dammi', 'voglio', 'vorrei', 'desidero',
            // List/enumeration words that don't change query intent
            'lista', 'elenco', 'elenchi', 'quali', 'quale', 'quanti', 'quante',
            'tutta', 'tante', 'alcune', 'alcuni', 'cosa', 'cose',
        ];

        $words = explode(' ', $normalized);
        $filtered = [];

        foreach ($words as $word) {
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $filtered[] = $word;
            }
        }

        // Apply synonym mapping
        $synonymMap = self::getQuerySynonymMap();
        $semanticWords = [];

        foreach ($filtered as $word) {
            $semanticWords[] = $synonymMap[$word] ?? $word;
        }

        // Remove duplicates and sort for consistency
        $semanticWords = array_unique($semanticWords);
        sort($semanticWords);

        return implode(' ', $semanticWords);
    }

    /**
     * Store search results in cache with AI analysis and embedding
     */
    public static function storeCache(string $siteUrl, string $query, array $results, int $pagesVisited, ?string $aiAnalysis = null, int $ttl = null): void
    {
        $ttl = $ttl ?? config('webscraper.search_cache.ttl', 604800); // 7 days default
        $queryHash = self::hashQuery($query);

        // Generate embedding for semantic similarity search
        $embeddingService = app(\Modules\WebScraper\Services\EmbeddingService::class);
        $embedding = $embeddingService->generateEmbedding($query);
        $embeddingJson = $embedding ? $embeddingService->encodeEmbedding($embedding) : null;

        if ($embeddingJson === null) {
            Log::channel('webscraper')->warning('SearchResultCache: Failed to generate embedding for query', [
                'query' => $query,
            ]);
        }

        $data = [
            'site_url' => $siteUrl,
            'query_hash' => $queryHash,
            'original_query' => $query,
            'query_embedding' => $embeddingJson,
            'results_json' => json_encode($results),
            'ai_analysis' => $aiAnalysis,
            'pages_visited' => $pagesVisited,
            'created_at' => now(),
            'expires_at' => now()->addSeconds($ttl),
        ];

        // Use REPLACE to handle duplicates (SQLite upsert)
        DB::connection('webscraper')->statement(
            "REPLACE INTO search_result_cache (site_url, query_hash, original_query, query_embedding, results_json, ai_analysis, pages_visited, created_at, expires_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['site_url'],
                $data['query_hash'],
                $data['original_query'],
                $data['query_embedding'],
                $data['results_json'],
                $data['ai_analysis'],
                $data['pages_visited'],
                $data['created_at']->toDateTimeString(),
                $data['expires_at']->toDateTimeString(),
            ]
        );

        Log::channel('webscraper')->info('SearchResultCache: Results cached', [
            'site_url' => $siteUrl,
            'query' => $query,
            'results_count' => count($results),
            'has_ai_analysis' => !empty($aiAnalysis),
            'expires_at' => $data['expires_at'],
        ]);
    }

    /**
     * Clean expired cache entries
     */
    public static function cleanExpired(): int
    {
        $deleted = self::where('expires_at', '<=', now())->delete();

        if ($deleted > 0) {
            Log::channel('webscraper')->info('SearchResultCache: Cleaned expired entries', [
                'deleted_count' => $deleted,
            ]);
        }

        return $deleted;
    }

    /**
     * Invalidate all cache for a specific site
     */
    public static function invalidateSite(string $siteUrl): int
    {
        $deleted = self::where('site_url', $siteUrl)->delete();

        Log::channel('webscraper')->info('SearchResultCache: Invalidated site cache', [
            'site_url' => $siteUrl,
            'deleted_count' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Hash query for consistent lookup (normalizes similar queries)
     * Uses aggressive semantic grouping to match similar queries
     */
    protected static function hashQuery(string $query): string
    {
        // Normalize query: lowercase, trim, remove extra spaces
        $normalized = trim(strtolower($query));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // Aggressive stop words removal - remove ALL articles, prepositions, common verbs
        $stopWords = [
            // Articles
            'il', 'lo', 'la', 'i', 'gli', 'le', 'un', 'uno', 'una',
            // Prepositions and contractions
            'di', 'a', 'da', 'in', 'con', 'su', 'per', 'tra', 'fra',
            'del', 'dello', 'della', 'dei', 'degli', 'delle',
            'al', 'allo', 'alla', 'ai', 'agli', 'alle',
            'dal', 'dallo', 'dalla', 'dai', 'dagli', 'dalle',
            'nel', 'nello', 'nella', 'nei', 'negli', 'nelle',
            'sul', 'sullo', 'sulla', 'sui', 'sugli', 'sulle',
            // Common function words
            'che', 'dell', 'dall', 'all', 'qual', 'questa', 'questo', 'tutti', 'tutte', 'tutto',
            // Common verbs that don't add semantic value
            'sono', 'è', 'ha', 'hanno', 'essere', 'avere',
            'offre', 'offrono', 'offerte', 'offerti', 'offerta', 'offerto',
            // Action verbs that don't change query intent
            'trova', 'cerca', 'cercare', 'trovare', 'mostra', 'vedere', 'visualizza',
            'dimmi', 'dammi', 'voglio', 'vorrei', 'desidero',
            // List/enumeration words that don't change query intent
            'lista', 'elenco', 'elenchi', 'quali', 'quale', 'quanti', 'quante',
            'tutta', 'tante', 'alcune', 'alcuni', 'cosa', 'cose',
        ];

        $words = explode(' ', $normalized);

        // Keep only meaningful words (length > 2) and not in stop words
        $filtered = [];
        foreach ($words as $word) {
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $filtered[] = $word;
            }
        }

        // Apply synonym normalization to create semantic buckets
        $synonymMap = self::getQuerySynonymMap();
        $semanticWords = [];

        foreach ($filtered as $word) {
            $semanticWords[] = $synonymMap[$word] ?? $word;
        }

        // Sort to ensure "contatti azienda" = "azienda contatti"
        sort($semanticWords);
        $normalized = implode(' ', $semanticWords);

        return md5($normalized);
    }

    /**
     * Map of query-level synonyms for semantic grouping
     * These are specifically for query intent matching, not content matching
     */
    protected static function getQuerySynonymMap(): array
    {
        return [
            // Contact synonyms - all map to 'contatto'
            'contatti' => 'contatto',
            'contatto' => 'contatto',
            'recapiti' => 'contatto',
            'recapito' => 'contatto',
            'telefono' => 'contatto',
            'email' => 'contatto',
            'indirizzo' => 'contatto',
            'dove' => 'contatto',
            'ubicazione' => 'contatto',

            // Company/business synonyms - all map to 'azienda'
            'azienda' => 'azienda',
            'aziendali' => 'azienda',
            'aziendale' => 'azienda',
            'societa' => 'azienda',
            'società' => 'azienda',
            'impresa' => 'azienda',
            'ditta' => 'azienda',

            // Services synonyms - all map to 'servizio'
            'servizi' => 'servizio',
            'servizio' => 'servizio',
            'soluzioni' => 'servizio',
            'soluzione' => 'servizio',
            'attivita' => 'servizio',
            'attività' => 'servizio',
            'offerte' => 'servizio',
            'offerta' => 'servizio',

            // Product synonyms - all map to 'prodotto'
            'prodotti' => 'prodotto',
            'prodotto' => 'prodotto',
            'articoli' => 'prodotto',
            'articolo' => 'prodotto',
            'catalogo' => 'prodotto',

            // Price synonyms - all map to 'prezzo'
            'prezzi' => 'prezzo',
            'prezzo' => 'prezzo',
            'costo' => 'prezzo',
            'costi' => 'prezzo',
            'tariffe' => 'prezzo',
            'tariffa' => 'prezzo',

            // Action verbs - normalize to base form
            'trova' => 'trova',
            'cerca' => 'trova',
            'cercare' => 'trova',
            'trovare' => 'trova',
            'mostra' => 'mostra',
            'vedere' => 'mostra',
            'visualizza' => 'mostra',
        ];
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        $total = self::count();
        $expired = self::where('expires_at', '<=', now())->count();
        $active = $total - $expired;

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
        ];
    }

    /**
     * Normalize URL to base domain for consistent cache lookups
     * Removes path, query strings, and fragments, keeping only scheme + host
     *
     * Examples:
     * - https://www.machebuoni.it/tagliatelle-con-ragu → https://www.machebuoni.it
     * - https://isofin.it/soluzioni/?page=1 → https://isofin.it
     * - http://example.com:8080/path → http://example.com:8080
     */
    protected static function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['host'])) {
            // If URL is malformed, return as-is
            return $url;
        }

        $normalized = ($parsed['scheme'] ?? 'https') . '://' . $parsed['host'];

        // Include port if specified and not default
        if (isset($parsed['port'])) {
            $isDefaultPort = ($parsed['scheme'] === 'https' && $parsed['port'] === 443) ||
                             ($parsed['scheme'] === 'http' && $parsed['port'] === 80);

            if (!$isDefaultPort) {
                $normalized .= ':' . $parsed['port'];
            }
        }

        return $normalized;
    }
}