<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiAnalyzerService
{
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('openapi.key');
        $this->model = config('webscraper.ai_analysis.model', 'gpt-4o');
        $this->maxTokens = config('webscraper.ai_analysis.max_tokens', 2000);
    }

    /**
     * Analyze scraped website content with AI
     */
    public function analyze(array $scrapedData, string $analysisType = 'business'): array
    {
        // Check cache first
        $cacheKey = $this->getCacheKey($scrapedData['url'], $analysisType);
        if ($cached = Cache::get($cacheKey)) {
            Log::info('AiAnalyzer: Using cached analysis', ['url' => $scrapedData['url']]);
            return $cached;
        }

        try {
            Log::info('AiAnalyzer: Starting analysis', [
                'url' => $scrapedData['url'],
                'type' => $analysisType,
            ]);

            // Prepare content for analysis
            $contentToAnalyze = $this->prepareContent($scrapedData);

            // Get appropriate prompt based on analysis type
            $systemPrompt = $this->getSystemPrompt($analysisType);

            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $this->buildUserPrompt($contentToAnalyze, $scrapedData),
                    ],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => 0.7,
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API error: ' . $response->body());
            }

            $result = $response->json();
            $analysis = $result['choices'][0]['message']['content'] ?? '';

            // Structure the response
            $structuredAnalysis = [
                'url' => $scrapedData['url'],
                'analyzed_at' => now()->toIso8601String(),
                'analysis_type' => $analysisType,
                'analysis' => $analysis,
                'metadata' => $scrapedData['metadata'] ?? [],
                'usage' => [
                    'prompt_tokens' => $result['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $result['usage']['completion_tokens'] ?? 0,
                    'total_tokens' => $result['usage']['total_tokens'] ?? 0,
                ],
            ];

            // Cache the result
            $ttl = config('webscraper.ai_analysis.cache_ttl', 3600);
            Cache::put($cacheKey, $structuredAnalysis, $ttl);

            Log::info('AiAnalyzer: Analysis completed', [
                'url' => $scrapedData['url'],
                'tokens' => $structuredAnalysis['usage']['total_tokens'],
            ]);

            return $structuredAnalysis;

        } catch (\Exception $e) {
            Log::error('AiAnalyzer: Analysis error', [
                'url' => $scrapedData['url'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Failed to analyze content: ' . $e->getMessage(),
                'url' => $scrapedData['url'] ?? 'unknown',
            ];
        }
    }

    /**
     * Analyze business information specifically
     */
    public function analyzeBusinessInfo(array $scrapedData): array
    {
        return $this->analyze($scrapedData, 'business');
    }

    /**
     * Extract key information like contacts, services, products
     */
    public function extractKeyInfo(array $scrapedData): array
    {
        return $this->analyze($scrapedData, 'extraction');
    }

    /**
     * Summarize website content
     */
    public function summarize(array $scrapedData): array
    {
        return $this->analyze($scrapedData, 'summary');
    }

    /**
     * Extract specific information from website based on custom query
     */
    public function extractCustomInfo(array $scrapedData, string $query): array
    {
        // Check cache first with query-specific key
        $cacheKey = $this->getCacheKey($scrapedData['url'], 'custom_' . md5($query));
        if ($cached = Cache::get($cacheKey)) {
            Log::info('AiAnalyzer: Using cached custom analysis', ['url' => $scrapedData['url'], 'query' => $query]);
            return $cached;
        }

        // Sanitize scraped data to prevent UTF-8 encoding errors
        $scrapedData = $this->sanitizeArrayForJson($scrapedData);

        try {
            Log::info('AiAnalyzer: Starting custom analysis', [
                'url' => $scrapedData['url'],
                'query' => $query,
            ]);

            // Prepare content for analysis
            $contentToAnalyze = $this->prepareContent($scrapedData);

            // Custom system prompt for targeted extraction
            $systemPrompt = <<<PROMPT
Sei un esperto nell'estrazione di informazioni specifiche da contenuti web.
Il tuo compito è analizzare il contenuto del sito web fornito e rispondere alla richiesta specifica dell'utente.

Regole:
1. Rispondi SOLO con le informazioni richieste dall'utente
2. Se le informazioni non sono presenti nel sito, dillo chiaramente
3. Sii conciso ma completo
4. Mantieni un formato strutturato e leggibile
5. Rispondi sempre in italiano
6. IMPORTANTE: Mantieni i numeri di telefono, partite IVA, codici fiscali ESATTAMENTE come appaiono. NON convertire in parole (es: 320.42.06795 rimane 320.42.06795, NON "tre due zero")
PROMPT;

            // Build user prompt with the specific query
            $userPrompt = $this->buildCustomQueryPrompt($contentToAnalyze, $scrapedData, $query);

            // Log the actual content being sent to AI for debugging number conversion issues
            Log::debug('AiAnalyzer: Content sent to AI', [
                'url' => $scrapedData['url'],
                'query' => $query,
                'content_preview' => substr($contentToAnalyze['main_content'] ?? '', 0, 500),
            ]);

            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $systemPrompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => $userPrompt,
                    ],
                ],
                'max_tokens' => $this->maxTokens,
                'temperature' => 0.3, // Lower temperature for more focused extraction
            ]);

            if (!$response->successful()) {
                throw new \Exception('OpenAI API error: ' . $response->body());
            }

            $result = $response->json();
            $analysis = $result['choices'][0]['message']['content'] ?? '';

            // Structure the response
            $structuredAnalysis = [
                'url' => $scrapedData['url'],
                'analyzed_at' => now()->toIso8601String(),
                'analysis_type' => 'custom',
                'query' => $query,
                'analysis' => $analysis,
                'metadata' => $scrapedData['metadata'] ?? [],
                'usage' => [
                    'prompt_tokens' => $result['usage']['prompt_tokens'] ?? 0,
                    'completion_tokens' => $result['usage']['completion_tokens'] ?? 0,
                    'total_tokens' => $result['usage']['total_tokens'] ?? 0,
                ],
            ];

            // Cache the result
            $ttl = config('webscraper.ai_analysis.cache_ttl', 3600);
            Cache::put($cacheKey, $structuredAnalysis, $ttl);

            Log::info('AiAnalyzer: Custom analysis completed', [
                'url' => $scrapedData['url'],
                'query' => $query,
                'tokens' => $structuredAnalysis['usage']['total_tokens'],
            ]);

            return $structuredAnalysis;

        } catch (\Exception $e) {
            Log::error('AiAnalyzer: Custom analysis error', [
                'url' => $scrapedData['url'] ?? 'unknown',
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Failed to analyze content: ' . $e->getMessage(),
                'url' => $scrapedData['url'] ?? 'unknown',
                'query' => $query,
            ];
        }
    }

    /**
     * Prepare content for AI analysis
     */
    protected function prepareContent(array $scrapedData): array
    {
        return [
            'title' => $scrapedData['metadata']['title'] ?? '',
            'description' => $scrapedData['metadata']['description'] ?? '',
            'main_content' => $scrapedData['content']['main'] ?? '',
            'headings' => array_slice($scrapedData['content']['headings'] ?? [], 0, 10),
            'paragraphs' => array_slice($scrapedData['content']['structured']['paragraphs'] ?? [], 0, 10),
        ];
    }

    /**
     * Build user prompt with scraped content
     */
    protected function buildUserPrompt(array $content, array $scrapedData): string
    {
        $url = $scrapedData['url'];
        $title = $content['title'];
        $description = $content['description'];

        // Use header + footer approach to capture complete page info
        $fullContent = $content['main_content'];
        $contentLength = strlen($fullContent);

        if ($contentLength <= 12000) {
            $mainContent = $fullContent;
        } else {
            // Extract header (first 8000) + footer (last 4000)
            $header = substr($fullContent, 0, 8000);
            $footer = substr($fullContent, -4000);
            $mainContent = $header . "\n\n[...contenuto intermedio omesso...]\n\n" . $footer;
        }

        $headings = '';
        foreach ($content['headings'] as $heading) {
            $headings .= "- (H{$heading['level']}) {$heading['text']}\n";
        }

        $paragraphs = '';
        foreach (array_slice($content['paragraphs'], 0, 5) as $p) {
            $paragraphs .= "- " . substr($p, 0, 200) . "...\n";
        }

        return <<<PROMPT
Analizza il seguente sito web e fornisci informazioni dettagliate:

URL: {$url}
Titolo: {$title}
Descrizione: {$description}

Intestazioni principali:
{$headings}

Alcuni paragrafi:
{$paragraphs}

Contenuto principale:
{$mainContent}

Analizza questo sito web e fornisci le informazioni richieste.
PROMPT;
    }

    /**
     * Build custom query prompt with scraped content
     */
    protected function buildCustomQueryPrompt(array $content, array $scrapedData, string $query): string
    {
        $url = $scrapedData['url'];
        $title = $content['title'];
        $description = $content['description'];

        // For contact queries, use full content to ensure footer data is included
        // Otherwise limit to 12000 chars (header ~8000 + footer ~4000)
        $fullContent = $content['main_content'];
        $contentLength = strlen($fullContent);

        if ($contentLength <= 12000) {
            $mainContent = $fullContent;
        } else {
            // Extract header (first 8000) + footer (last 4000) to capture both navigation and contact info
            $header = substr($fullContent, 0, 8000);
            $footer = substr($fullContent, -4000);
            $mainContent = $header . "\n\n[...contenuto intermedio omesso...]\n\n" . $footer;
        }

        $headings = '';
        foreach ($content['headings'] as $heading) {
            $headings .= "- (H{$heading['level']}) {$heading['text']}\n";
        }

        return <<<PROMPT
Contenuto del sito web da analizzare:

URL: {$url}
Titolo: {$title}
Descrizione: {$description}

Intestazioni principali:
{$headings}

Contenuto completo:
{$mainContent}

---

RICHIESTA DELL'UTENTE:
{$query}

Rispondi alla richiesta dell'utente basandoti sul contenuto del sito web fornito sopra.
PROMPT;
    }

    /**
     * Get system prompt based on analysis type
     */
    protected function getSystemPrompt(string $analysisType): string
    {
        return match ($analysisType) {
            'business' => <<<PROMPT
Sei un analista di business esperto. Analizza il sito web fornito ed estrai:

1. **Tipo di azienda**: Identifica il settore e l'industria
2. **Prodotti/Servizi**: Elenca i principali prodotti o servizi offerti
3. **Target di mercato**: Identifica il pubblico target
4. **Punti di forza**: Evidenzia i vantaggi competitivi
5. **Contatti**: Estrai informazioni di contatto (email, telefono, indirizzo)
   IMPORTANTE: Mantieni i numeri di telefono ESATTAMENTE come appaiono (es: 320.42.06795 o +39 320 4206795). NON convertire in parole.
6. **Presenza online**: Social media, altre piattaforme
7. **Proposizione di valore**: Qual è la loro proposta unica?

Fornisci una risposta strutturata in italiano con questi punti.
PROMPT,
            'extraction' => <<<PROMPT
Sei un esperto nell'estrazione di informazioni strutturate. Dal sito web fornito, estrai:

1. **Informazioni di contatto**:
   - Email
   - Telefono
   - Indirizzo fisico
   - Orari di apertura

2. **Prodotti/Servizi principali**:
   - Lista dei prodotti o servizi
   - Prezzi se disponibili

3. **Informazioni aziendali**:
   - Nome completo dell'azienda
   - Partita IVA / Codice fiscale se presente
   - Anno di fondazione

4. **Social media e collegamenti**:
   - Facebook, Instagram, LinkedIn, etc.

Rispondi in italiano con formato strutturato.
PROMPT,
            'summary' => <<<PROMPT
Sei un esperto nel riassumere contenuti web. Crea un riassunto conciso del sito web che includa:

1. **Panoramica**: Di cosa si occupa questo sito/azienda (2-3 frasi)
2. **Punti chiave**: 3-5 punti principali sul contenuto
3. **Call-to-action**: Quali azioni vuole che i visitatori compiano?
4. **Tono e stile**: Come comunica il brand?

Rispondi in italiano con un riassunto chiaro e professionale.
PROMPT,
            default => 'Analizza questo sito web e fornisci informazioni rilevanti in italiano.',
        };
    }

    /**
     * Get cache key for analysis
     */
    protected function getCacheKey(string $url, string $type): string
    {
        return 'ai_analysis:' . $type . ':' . md5($url);
    }

    /**
     * Search through multiple pages and extract information based on query
     * Analyzes each page individually, then aggregates results
     */
    public function searchMultiplePages(array $pagesData, string $query): array
    {
        Log::info('AiAnalyzer: Starting multi-page search', [
            'pages_count' => count($pagesData),
            'query' => $query,
        ]);

        if (empty($pagesData)) {
            return ['error' => 'No pages data provided'];
        }

        // Sanitize all pages data to prevent UTF-8 encoding errors
        $pagesData = $this->sanitizeArrayForJson($pagesData);

        if (empty($pagesData)) {
            return ['error' => 'No valid pages data after sanitization'];
        }

        try {
            // Analyze each page individually
            $individualResults = [];
            $totalTokens = 0;

            foreach ($pagesData as $index => $page) {
                Log::channel('webscraper')->info('AiAnalyzer: Analyzing individual page', [
                    'page_num' => $index + 1,
                    'total_pages' => count($pagesData),
                    'url' => $page['url'],
                ]);

                // Use extractCustomInfo for each page with retry logic
                $pageAnalysis = $this->extractCustomInfoWithRetry($page, $query, 3);

                if (!isset($pageAnalysis['error'])) {
                    $individualResults[] = [
                        'url' => $page['url'],
                        'title' => $page['metadata']['title'] ?? 'Untitled',
                        'analysis' => $pageAnalysis['analysis'],
                        'tokens' => $pageAnalysis['usage']['total_tokens'] ?? 0,
                    ];
                    $totalTokens += $pageAnalysis['usage']['total_tokens'] ?? 0;
                } else {
                    Log::channel('webscraper')->warning('AiAnalyzer: Page analysis failed after retries', [
                        'url' => $page['url'],
                        'error' => $pageAnalysis['error'],
                    ]);
                }

                // Delay to respect rate limits (1 seconds between calls)
                sleep(1);
            }

            Log::info('AiAnalyzer: Individual analyses completed', [
                'pages_with_results' => count($individualResults),
                'total_tokens_used' => $totalTokens,
            ]);

            // Now aggregate all results into final summary
            $aggregatedAnalysis = $this->aggregateIndividualResults($individualResults, $query);

            $structuredAnalysis = [
                'pages_analyzed' => count($pagesData),
                'pages_with_results' => count($individualResults),
                'analyzed_at' => now()->toIso8601String(),
                'query' => $query,
                'analysis' => $aggregatedAnalysis,
                'individual_results' => $individualResults,
                'usage' => [
                    'total_tokens' => $totalTokens,
                ],
            ];

            Log::info('AiAnalyzer: Multi-page search completed', [
                'pages_analyzed' => count($pagesData),
                'pages_with_results' => count($individualResults),
                'query' => $query,
                'total_tokens' => $totalTokens,
            ]);

            return $structuredAnalysis;

        } catch (\Exception $e) {
            Log::error('AiAnalyzer: Multi-page search error', [
                'pages_count' => count($pagesData),
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'Failed to search pages: ' . $e->getMessage(),
                'pages_count' => count($pagesData),
                'query' => $query,
            ];
        }
    }

    /**
     * Aggregate content from multiple pages
     */
    protected function aggregatePageContents(array $pagesData): array
    {
        $aggregated = [];

        foreach ($pagesData as $index => $page) {
            $pageNum = $index + 1;
            $url = $page['url'];
            $title = $page['metadata']['title'] ?? 'Untitled';
            $content = substr($page['content']['main'] ?? '', 0, 3000); // Limit each page
            $headings = array_slice($page['content']['headings'] ?? [], 0, 5);

            $headingsText = '';
            foreach ($headings as $h) {
                $headingsText .= "  - (H{$h['level']}) {$h['text']}\n";
            }

            $aggregated[] = [
                'page_number' => $pageNum,
                'url' => $url,
                'title' => $title,
                'content' => $content,
                'headings' => $headingsText,
            ];
        }

        return $aggregated;
    }

    /**
     * Build search prompt for multi-page analysis
     */
    protected function buildMultiPageSearchPrompt(array $aggregatedContent, string $query): string
    {
        $pagesText = '';

        foreach ($aggregatedContent as $page) {
            $pagesText .= "═══ PAGINA {$page['page_number']} ═══\n";
            $pagesText .= "URL: {$page['url']}\n";
            $pagesText .= "Titolo: {$page['title']}\n";
            $pagesText .= "Intestazioni:\n{$page['headings']}\n";
            $pagesText .= "Contenuto:\n{$page['content']}\n\n";
        }

        return <<<PROMPT
Ho scansionato {$this->countPages($aggregatedContent)} pagine del sito web. Ecco i contenuti:

{$pagesText}

═══ RICHIESTA DELL'UTENTE ═══
{$query}

Cerca le informazioni richieste attraverso TUTTE le pagine fornite sopra.
Per ogni informazione trovata, indica da quale pagina proviene (es: "Pagina 2: ...").
Organizza i risultati in modo chiaro e strutturato.
PROMPT;
    }

    /**
     * Count pages helper
     */
    protected function countPages(array $aggregatedContent): int
    {
        return count($aggregatedContent);
    }

    /**
     * Clear analysis cache for a URL
     */
    public function clearCache(string $url, ?string $type = null): bool
    {
        if ($type) {
            return Cache::forget($this->getCacheKey($url, $type));
        }

        // Clear all analysis types for this URL
        $types = ['business', 'extraction', 'summary'];
        foreach ($types as $t) {
            Cache::forget($this->getCacheKey($url, $t));
        }

        return true;
    }

    /**
     * Aggregate individual analysis results into a final summary
     */
    protected function aggregateIndividualResults(array $individualResults, string $query): string
    {
        if (empty($individualResults)) {
            return "Nessuna informazione trovata nelle pagine analizzate.";
        }

        // Build summary from all individual results
        $summary = "# Risultati della ricerca: {$query}\n\n";
        $summary .= "Ho analizzato " . count($individualResults) . " pagine e trovato le seguenti informazioni:\n\n";

        $foundResults = 0;
        foreach ($individualResults as $index => $result) {
            $pageNum = $index + 1;

            // Skip pages with no relevant information
            if (stripos($result['analysis'], 'non') !== false &&
                (stripos($result['analysis'], 'non sono presenti') !== false ||
                 stripos($result['analysis'], 'non è presente') !== false ||
                 stripos($result['analysis'], 'non ho trovato') !== false)) {
                continue;
            }

            $foundResults++;
            $summary .= "## Pagina {$pageNum}: {$result['title']}\n";
            $summary .= "**URL**: {$result['url']}\n\n";
            $summary .= $result['analysis'] . "\n\n";
            $summary .= "---\n\n";
        }

        if ($foundResults === 0) {
            return "Ho analizzato " . count($individualResults) . " pagine ma non ho trovato informazioni rilevanti per la query: \"{$query}\".";
        }

        $summary .= "\n**Riepilogo**: Trovate informazioni rilevanti in {$foundResults} su " . count($individualResults) . " pagine analizzate.";

        return $summary;
    }

    /**
     * Extract custom info with exponential backoff retry logic
     */
    protected function extractCustomInfoWithRetry(array $scrapedData, string $query, int $maxRetries = 3): array
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                $result = $this->extractCustomInfo($scrapedData, $query);

                // If successful (no error), return immediately
                if (!isset($result['error'])) {
                    return $result;
                }

                // Check if it's a rate limit error
                if (str_contains($result['error'], 'rate_limit_exceeded') ||
                    str_contains($result['error'], 'Rate limit')) {

                    $lastError = $result['error'];

                    // Extract wait time from error message if available
                    $waitTime = $this->extractWaitTimeFromError($result['error']);

                    if ($attempt < $maxRetries) {
                        Log::channel('webscraper')->warning('AiAnalyzer: Rate limit hit, retrying', [
                            'attempt' => $attempt,
                            'max_retries' => $maxRetries,
                            'wait_time' => $waitTime,
                            'url' => $scrapedData['url'],
                        ]);

                        // Wait before retry (use suggested time or exponential backoff)
                        sleep($waitTime);
                        continue;
                    }
                }

                // For other errors, return immediately
                return $result;

            } catch (\Exception $e) {
                $lastError = $e->getMessage();

                if ($attempt < $maxRetries) {
                    $waitTime = pow(2, $attempt); // Exponential backoff: 2, 4, 8 seconds
                    Log::channel('webscraper')->warning('AiAnalyzer: Exception occurred, retrying', [
                        'attempt' => $attempt,
                        'max_retries' => $maxRetries,
                        'wait_time' => $waitTime,
                        'error' => $e->getMessage(),
                    ]);
                    sleep($waitTime);
                    continue;
                }
            }
        }

        // All retries exhausted
        return [
            'error' => "Failed after {$maxRetries} attempts. Last error: " . $lastError,
            'url' => $scrapedData['url'] ?? 'unknown',
            'query' => $query,
        ];
    }

    /**
     * Extract wait time from OpenAI rate limit error message
     */
    protected function extractWaitTimeFromError(string $errorMessage): int
    {
        // Try to extract wait time from error message
        // Example: "Please try again in 2.374s"
        if (preg_match('/try again in ([\d.]+)s/', $errorMessage, $matches)) {
            return (int) ceil((float) $matches[1]);
        }

        // Example: "Please try again in 736ms"
        if (preg_match('/try again in ([\d]+)ms/', $errorMessage, $matches)) {
            return (int) ceil((float) $matches[1] / 1000);
        }

        // Default exponential backoff
        return 3; // 3 seconds default
    }

    /**
     * Sanitize data recursively to prevent UTF-8 encoding errors
     */
    protected function sanitizeArrayForJson($data)
    {
        if (is_string($data)) {
            // Remove invalid UTF-8 characters
            $data = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Remove non-printable characters except newlines and tabs
            $data = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $data);
            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->sanitizeArrayForJson($value);
            }
            return $data;
        }

        return $data;
    }
}