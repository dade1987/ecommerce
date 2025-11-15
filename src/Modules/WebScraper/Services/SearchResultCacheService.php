<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Modules\WebScraper\Models\SearchResultCache;

class SearchResultCacheService
{
    protected IntelligentCrawlerService $crawler;

    public function __construct(IntelligentCrawlerService $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * Get cached search results or perform new search
     * If cached, reformulate the response for variety
     */
    public function getCachedOrSearch(string $url, string $query, int $maxDepth = 3): array
    {
        // Check cache first
        $cached = SearchResultCache::getCached($url, $query);

        if ($cached !== null) {

            $cacheCount = is_array($cached['results']) ? count($cached) : 0;
            Log::channel('webscraper')->info('SearchResultCache: Using cached results', [
                'url' => $url,
                'query' => $query,
                'results_count' => $cacheCount,
            ]);

            // Reformulate the response for variety
            $reformulated = $this->reformulateResponse($cached, $query);

            return [
                'query' => $query,
                'pages_visited' => $cached['pages_visited'],
                'results' => $cached['results'],
                'reformulated_summary' => $reformulated,
                'from_cache' => true,
                'cached_at' => $cached['cached_at'],
            ];
        }

        // Cache miss - perform new search
        Log::channel('webscraper')->info('SearchResultCache: Performing new search', [
            'url' => $url,
            'query' => $query,
        ]);

        $searchResults = $this->crawler->intelligentSearch($url, $query, $maxDepth);

        // Cache the results
        $this->cacheResults($url, $query, $searchResults['results'], $searchResults['pages_visited']);

        return [
            'query' => $searchResults['query'],
            'pages_visited' => $searchResults['pages_visited'],
            'results' => $searchResults['results'],
            'from_cache' => false,
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
