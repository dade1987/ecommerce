<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Modules\WebScraper\Models\SearchResultCache;

class SearchResultCacheService
{
    protected IntelligentCrawlerService $crawler;
    protected EmbeddingService $embeddingService;

    public function __construct(IntelligentCrawlerService $crawler, EmbeddingService $embeddingService)
    {
        $this->crawler = $crawler;
        $this->embeddingService = $embeddingService;
    }

    /**
     * Get cached search results or perform new search
     * Uses semantic similarity search to match related queries
     */
    public function getCachedOrSearch(string $url, string $query, int $maxDepth = 3): array
    {
        // Step 1: Check for exact match in cache
        $cached = SearchResultCache::getCached($url, $query);

        if ($cached !== null) {
            Log::channel('webscraper')->info('SearchResultCache: Exact cache hit', [
                'url' => $url,
                'query' => $query,
            ]);

            return $this->returnCachedResult($cached, $query, 'exact');
        }

        // Step 2: Try semantic similarity search
        Log::channel('webscraper')->info('SearchResultCache: No exact match, trying similarity search', [
            'url' => $url,
            'query' => $query,
        ]);

        $similarCached = $this->findSimilarCached($url, $query);

        if ($similarCached !== null) {
            Log::channel('webscraper')->info('SearchResultCache: Similar query found in cache', [
                'original_query' => $query,
                'matched_query' => $similarCached['matched_query'],
                'similarity' => $similarCached['similarity'],
            ]);

            return $this->returnCachedResult($similarCached['cache_data'], $query, 'similarity');
        }

        // Step 3: Cache miss - perform new search
        Log::channel('webscraper')->info('SearchResultCache: No similar match, performing new search', [
            'url' => $url,
            'query' => $query,
        ]);

        $searchResults = $this->crawler->intelligentSearch($url, $query, $maxDepth);

        // Cache the results with embedding
        $this->cacheResults($url, $query, $searchResults['results'], $searchResults['pages_visited']);

        return [
            'query' => $searchResults['query'],
            'pages_visited' => $searchResults['pages_visited'],
            'results' => $searchResults['results'],
            'from_cache' => false,
            'match_type' => 'new_search',
        ];
    }

    /**
     * Find similar cached queries using semantic similarity
     */
    protected function findSimilarCached(string $url, string $query, float $threshold = 0.75): ?array
    {
        try {
            // Get all cached queries for this URL
            $allCached = SearchResultCache::where('url', $url)
                ->where('expires_at', '>', now())
                ->get();

            if ($allCached->isEmpty()) {
                Log::channel('webscraper')->debug('SearchResultCache: No cached queries for URL', ['url' => $url]);
                return null;
            }

            // Prepare candidates with embeddings
            $candidates = [];
            foreach ($allCached as $cache) {
                $embedding = $this->embeddingService->decodeEmbedding($cache->query_embedding);

                if ($embedding !== null) {
                    $candidates[] = [
                        'query' => $cache->query,
                        'embedding' => $embedding,
                        'cache_data' => [
                            'query' => $cache->query,
                            'results' => json_decode($cache->results, true),
                            'pages_visited' => $cache->pages_visited,
                            'ai_analysis' => $cache->ai_analysis,
                            'cached_at' => $cache->created_at->toIso8601String(),
                        ],
                    ];
                }
            }

            if (empty($candidates)) {
                Log::channel('webscraper')->debug('SearchResultCache: No candidates with embeddings');
                return null;
            }

            // Find most similar query
            $match = $this->embeddingService->findMostSimilar($query, $candidates, $threshold);

            if ($match === null) {
                return null;
            }

            return [
                'matched_query' => $match['query'],
                'similarity' => $match['similarity'],
                'cache_data' => $match['candidate']['cache_data'],
            ];

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('SearchResultCache: Error in similarity search', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Return cached result with reformulation
     */
    protected function returnCachedResult(array $cached, string $currentQuery, string $matchType): array
    {
        $cacheCount = is_array($cached['results']) ? count($cached['results']) : 0;

        Log::channel('webscraper')->info('SearchResultCache: Using cached results', [
            'current_query' => $currentQuery,
            'cached_query' => $cached['query'] ?? 'unknown',
            'results_count' => $cacheCount,
            'match_type' => $matchType,
        ]);

        // Reformulate the response for variety and current query
        $reformulated = $this->reformulateResponse($cached, $currentQuery);

        return [
            'query' => $currentQuery,
            'pages_visited' => $cached['pages_visited'],
            'results' => $cached['results'],
            'reformulated_summary' => $reformulated,
            'from_cache' => true,
            'cached_at' => $cached['cached_at'] ?? null,
            'match_type' => $matchType,
            'original_cached_query' => $cached['query'] ?? null,
        ];
    }

    /**
     * Cache search results with optional AI analysis
     */
    public function cacheResults(string $url, string $query, array $results, int $pagesVisited, ?string $aiAnalysis = null): void
    {
        $ttl = config('webscraper.search_cache.ttl', 604800); // 7 days default

        SearchResultCache::storeCache($url, $query, $results, $pagesVisited, $aiAnalysis, $ttl);
    }

    /**
     * Reformulate cached response using GPT-4o with previous AI analysis as reference
     * This ensures each response feels fresh even when using cached data
     */
    protected function reformulateResponse(array $cachedData, string $currentQuery): string
    {
        try {
            $results = $cachedData['results'];

            if (empty($results)) {
                return "Non ho trovato risultati rilevanti nella cache per questa query.";
            }

            // If we have a previous AI analysis, use it as primary reference
            $previousAnalysis = $cachedData['ai_analysis'] ?? null;

            // Build a summary of cached results with longer excerpts for better context
            $resultsSummary = "Risultati trovati:\n\n";
            foreach (array_slice($results, 0, 5) as $index => $result) {
                $title = $result['title'] ?? 'Untitled';
                $url = $result['url'] ?? '';
                $excerpt = $result['content_excerpt'] ?? '';
                // Use very long excerpts (2000 chars) to ensure all details are included
                $excerptText = strlen($excerpt) > 2000 ? substr($excerpt, 0, 2000) . "..." : $excerpt;
                $resultsSummary .= ($index + 1) . ". **{$title}**\n   URL: {$url}\n   Contenuto: {$excerptText}\n\n";
            }

            $systemPrompt = <<<PROMPT
Sei un assistente che ESTRAE DATI SPECIFICI da contenuti web in modo COMPLETO e DETTAGLIATO.

REGOLE CRITICHE:
1. ESTRAI TUTTE le informazioni rilevanti trovate nel contenuto
2. Se l'utente chiede CONTATTI: mostra ESPLICITAMENTE telefono, email, indirizzo
3. Se l'utente chiede SOLUZIONI/SERVIZI/PRODOTTI: elenca TUTTI quelli trovati con dettagli
4. NON limitarti a 2-3 elementi - ELENCA TUTTO ciò che trovi
5. NON essere generico - ESTRAI dati specifici dal contenuto
6. Struttura la risposta con elenchi puntati o numerati per chiarezza
7. Includi sempre gli URL delle fonti
8. Se hai accesso a un'analisi precedente, usala come riferimento per assicurare completezza

ESEMPIO BUONO per CONTATTI:
"I contatti di ISOFIN sono:
- Telefono: 051 683 6036
- Email: info@isofin.it
- Indirizzo: Via Ferrarese, 41 - 44042 Cento (FE)
Fonte: [URL]"

ESEMPIO BUONO per SOLUZIONI:
"Isofin offre le seguenti soluzioni:
1. Infissi in Alluminio - design moderno, sicuri...
2. Infissi in PVC - ideali per...
3. Finestre in Legno - eleganti...
4. Portoncini d'Ingresso - artigianali...
5. Grate di Sicurezza - protezione...
[continua con TUTTE le soluzioni trovate]"

ESEMPIO CATTIVO:
"Isofin offre infissi in alluminio e PVC. Per ulteriori dettagli visita [URL]"
PROMPT;

            // Add previous analysis as context if available
            $previousAnalysisSection = '';
            if (!empty($previousAnalysis)) {
                $previousAnalysisSection = <<<PROMPT

RISPOSTA PRECEDENTE (usa come riferimento per completezza):
{$previousAnalysis}

IMPORTANTE: Usa la risposta precedente come GUIDA per assicurarti di includere TUTTE le informazioni rilevanti.
Se la query attuale è simile, mantieni lo stesso livello di dettaglio e completezza.
PROMPT;
            }

            $userPrompt = <<<PROMPT
Query utente: "{$currentQuery}"
{$previousAnalysisSection}

{$resultsSummary}

Totale risultati: {$cachedData['pages_visited']} pagine visitate

Analizza i risultati sopra e rispondi SPECIFICAMENTE alla query dell'utente.
Se l'utente chiede contatti, estrai e mostra chiaramente: telefono, email, indirizzo.
Se chiede servizi/prodotti, descrivi in dettaglio cosa offre l'azienda.
Sii diretto e preciso nella risposta.
PROMPT;

            $apiKey = config('openapi.key');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o', // Using gpt-4o instead of mini for better instruction following
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 2000, // Increased to allow very detailed responses
                'temperature' => 0.2, // Very low temperature for precise, complete extraction
            ]);

            if (!$response->successful()) {
                Log::channel('webscraper')->error('SearchResultCache: Reformulation failed', [
                    'error' => $response->body(),
                ]);
                return $this->buildDefaultSummary($results);
            }

            $result = $response->json();
            $reformulated = trim($result['choices'][0]['message']['content'] ?? '');

            if (empty($reformulated)) {
                return $this->buildDefaultSummary($results);
            }

            Log::channel('webscraper')->info('SearchResultCache: Response reformulated successfully', [
                'tokens_used' => $result['usage']['total_tokens'] ?? 0,
            ]);

            return $reformulated;

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('SearchResultCache: Reformulation error', [
                'error' => $e->getMessage(),
            ]);
            return $this->buildDefaultSummary($cachedData['results']);
        }
    }

    /**
     * Build a default summary if reformulation fails
     */
    protected function buildDefaultSummary(array $results): string
    {
        if (empty($results)) {
            return "Nessun risultato trovato.";
        }

        $summary = "Ho trovato " . count($results) . " risultati rilevanti:\n\n";

        foreach (array_slice($results, 0, 5) as $index => $result) {
            $title = $result['title'] ?? 'Untitled';
            $url = $result['url'] ?? '';
            $summary .= ($index + 1) . ". {$title}\n   {$url}\n\n";
        }

        return $summary;
    }

    /**
     * Clean expired cache entries
     */
    public function cleanExpired(): int
    {
        return SearchResultCache::cleanExpired();
    }

    /**
     * Invalidate cache for a specific site
     */
    public function invalidateSite(string $url): int
    {
        return SearchResultCache::invalidateSite($url);
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return SearchResultCache::getStats();
    }

    /**
     * Check if a query has cached results
     */
    public function hasCached(string $url, string $query): bool
    {
        return SearchResultCache::getCached($url, $query) !== null;
    }
}
