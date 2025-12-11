<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Models\WebscraperChunk;
use Modules\WebScraper\Traits\EnhancesSearchQueries;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Exception;

/**
 * ClientSiteQaService
 *
 * Q&A service for client sites using RAG (Retrieval-Augmented Generation):
 * 1. Generates query embedding
 * 2. Uses MongoDB Atlas Vector Search to find similar chunks
 * 3. Builds context from top K chunks
 * 4. Calls LLM with context to generate answer
 */
class ClientSiteQaService
{
    use EnhancesSearchQueries;

    protected EmbeddingService $embeddingService;

    // Vector search configuration
    protected int $topK;
    protected float $minSimilarity;
    protected int $numCandidates;
    protected string $indexName;

    // LLM configuration
    protected string $llmModel;
    protected float $llmTemperature;
    protected int $llmMaxTokens;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;

        // Load configuration
        $this->topK = config('webscraper.rag.vector_search.top_k', 10);
        $this->minSimilarity = config('webscraper.rag.vector_search.min_similarity', 0.7);
        $this->numCandidates = config('webscraper.rag.vector_search.num_candidates', 100);
        $this->indexName = config('webscraper.rag.vector_search.index_name', 'vector_index_1');

        $this->llmModel = config('webscraper.rag.llm.model', 'gpt-3.5-turbo');
        $this->llmTemperature = config('webscraper.rag.llm.temperature', 0.7);
        $this->llmMaxTokens = config('webscraper.rag.llm.max_tokens', 1000);
    }

    /**
     * Answer a question using RAG over indexed sites
     *
     * @param string $query User's question
     * @param string|null $domain Optional: restrict search to specific domain
     * @return array Answer with sources and metadata
     * @throws Exception
     */
    public function answerQuestion(string $query, ?string $domain = null): array
    {
        Log::channel('webscraper')->info('ClientSiteQa: Processing question', [
            'query' => $query,
            'domain' => $domain,
        ]);

        // Step 1: Generate query embedding
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Step 2: Vector search for similar chunks using Atlas Vector Search
        $similarChunks = $this->vectorSearch($queryEmbedding, $domain);

        if (empty($similarChunks)) {
            Log::channel('webscraper')->warning('ClientSiteQa: No relevant chunks found', [
                'query' => $query,
                'domain' => $domain,
            ]);

            return [
                'answer' => 'Non ho trovato informazioni rilevanti nei siti indicizzati per rispondere a questa domanda.',
                'sources' => [],
                'chunks_found' => 0,
                'method' => 'atlas_vector_search',
            ];
        }

        // Step 3: Build context from chunks
        $context = $this->buildContext($similarChunks);

        // Step 4: Generate answer using LLM
        $answer = $this->generateAnswer($query, $context);

        // Step 5: Extract sources
        $sources = $this->extractSources($similarChunks);

        Log::channel('webscraper')->info('ClientSiteQa: Answer generated', [
            'query' => $query,
            'chunks_found' => count($similarChunks),
            'sources_count' => count($sources),
        ]);

        return [
            'answer' => $answer,
            'sources' => $sources,
            'chunks_found' => count($similarChunks),
            'method' => 'atlas_vector_search',
        ];
    }

    /**
     * Perform vector search using MongoDB Atlas Vector Search
     *
     * @param array $queryEmbedding Query embedding vector (1536 dimensions)
     * @param string|null $domain Optional domain filter
     * @return array Array of similar chunks with scores
     */
    protected function vectorSearch(array $queryEmbedding, ?string $domain = null): array
    {
        // Build vector search stage
        $vectorSearchStage = [
            'index' => $this->indexName,
            'path' => 'embedding',
            'queryVector' => $queryEmbedding,
            'numCandidates' => $this->numCandidates,
            'limit' => $this->topK,
        ];

        // Domain filter (native pre-filtering with 'filter' parameter)
        // NOTE: Requires domain field to be indexed as filter in Atlas Vector Search index
        if ($domain) {
            // Normalize domain variants (both with and without www)
            $domainVariants = [$domain];

            if (str_starts_with($domain, 'www.')) {
                // If starts with www, also try without
                $domainVariants[] = substr($domain, 4);
            } else {
                // If no www, also try with www
                $domainVariants[] = 'www.' . $domain;
            }

            // Add native Atlas pre-filtering
            $vectorSearchStage['filter'] = [
                'domain' => ['$in' => $domainVariants]
            ];

            Log::channel('webscraper')->debug('ClientSiteQa: Domain pre-filter applied (Atlas native)', [
                'domain' => $domain,
                'variants' => $domainVariants,
            ]);
        }

        // MongoDB Atlas Vector Search aggregation pipeline
        $pipeline = [
            ['$vectorSearch' => $vectorSearchStage],
            [
                '$addFields' => [
                    'score' => ['$meta' => 'vectorSearchScore']
                ]
            ],
        ];

        // Execute aggregation
        try {
            $collection = DB::connection('mongodb')
                ->getCollection('webscraper_chunks');

            Log::channel('webscraper')->debug('ClientSiteQa: Executing vector search', [
                'database' => config('database.connections.mongodb.database'),
                'collection' => 'webscraper_chunks',
                'index_name' => 'vector_index_1',
                'embedding_dimensions' => count($queryEmbedding),
                'domain_filter' => $domain,
                'pipeline' => json_encode($pipeline, JSON_PRETTY_PRINT),
                'query_embedding_sample' => array_slice($queryEmbedding, 0, 5), // First 5 values
            ]);

            $results = $collection->aggregate($pipeline)->toArray();

            Log::channel('webscraper')->info('ClientSiteQa: Vector search completed', [
                'results_count' => count($results),
                'domain' => $domain,
            ]);

            // LOG DETTAGLIATO: Vediamo ESATTAMENTE cosa ritorna Atlas
            if (count($results) > 0) {
                Log::channel('webscraper')->debug('ClientSiteQa: Raw Atlas results (first 3)', [
                    'results' => array_slice(array_map(function($result) {
                        return [
                            '_id' => (string)$result['_id'],
                            'score' => $result['score'] ?? null,
                            'page_id' => isset($result['page_id']) ? (string)$result['page_id'] : null,
                            'chunk_index' => $result['chunk_index'] ?? null,
                            'content_preview' => isset($result['content']) ? substr($result['content'], 0, 100) : null,
                            'has_embedding' => isset($result['embedding']),
                        ];
                    }, $results), 0, 3),
                ]);
            } else {
                Log::channel('webscraper')->warning('ClientSiteQa: Atlas returned ZERO results', [
                    'pipeline_used' => json_encode($pipeline),
                    'embedding_first_5_values' => array_slice($queryEmbedding, 0, 5),
                    'embedding_last_5_values' => array_slice($queryEmbedding, -5),
                    'total_chunks_in_collection' => $collection->count(),
                ]);
            }

            // Convert MongoDB results to array
            // NOTE: Atlas Vector Search already returns the top K most similar chunks
            // Score is NOT in 0-1 range, it's a relevance score (higher = more similar)
            $chunks = [];
            foreach ($results as $result) {
                $score = $result['score'] ?? 0;

                $chunks[] = [
                    'chunk' => WebscraperChunk::find($result['_id']),
                    'score' => $score,
                ];
            }

            Log::channel('webscraper')->debug('ClientSiteQa: Chunks after processing', [
                'chunks_count' => count($chunks),
                'scores' => array_map(fn($c) => $c['score'], $chunks),
            ]);

            return $chunks;

        } catch (Exception $e) {
            Log::channel('webscraper')->error('ClientSiteQa: Vector search failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Build context string from similar chunks
     *
     * @param array $similarChunks Array of chunks with scores
     * @return string Context for LLM
     */
    protected function buildContext(array $similarChunks): string
    {
        $contextParts = [];

        foreach ($similarChunks as $index => $item) {
            $chunk = $item['chunk'];
            $score = $item['score'];

            if (!$chunk) {
                continue;
            }

            // Load page relationship
            $page = $chunk->page;

            $contextParts[] = sprintf(
                "[Source %d - %s (Score: %.2f)]\nTitle: %s\nURL: %s\n\n%s\n",
                $index + 1,
                $page->domain ?? 'Unknown',
                $score,
                $page->title ?? 'No title',
                $page->url ?? 'No URL',
                $chunk->content
            );
        }

        return implode("\n---\n\n", $contextParts);
    }

    /**
     * Generate answer using LLM with context
     *
     * @param string $query User's question
     * @param string $context Context from chunks
     * @return string Generated answer
     * @throws Exception
     */
    protected function generateAnswer(string $query, string $context): string
    {
        $systemPrompt = <<<EOT
Sei un assistente AI che risponde a domande basandoti ESCLUSIVAMENTE sulle informazioni fornite nel contesto.

REGOLE:
1. Rispondi SOLO usando le informazioni presenti nel contesto
2. Se il contesto non contiene informazioni sufficienti, dillo chiaramente
3. Cita le fonti quando possibile (es. "Secondo [Source 1]...")
4. Sii conciso ma completo
5. Usa un tono professionale e amichevole
6. Rispondi in italiano

CONTESTO:
{$context}
EOT;

        try {
            // Use vLLM if configured, otherwise fallback to OpenAI
            $vllmBaseUri = (string) config('services.vllm.base_uri', '');
            $vllmApiKey = (string) config('services.vllm.key', '');
            $vllmModel = (string) config('services.vllm.model', $this->llmModel);

            // Check for Groq first, then vLLM, then OpenAI
            $groqApiKey = (string) config('services.groq.key', '');
            $groqBaseUri = (string) config('services.groq.base_uri', 'https://api.groq.com/openai/v1');
            $groqModel = (string) config('services.groq.model', 'llama-3.1-70b-versatile');

            $useHttp = false;
            $httpBaseUri = '';
            $httpApiKey = '';
            $httpModel = '';

            if ($groqApiKey !== '') {
                // Use Groq OpenAI-compatible endpoint via HTTP
                $useHttp = true;
                $httpBaseUri = rtrim($groqBaseUri, '/');
                $httpApiKey = $groqApiKey;
                $httpModel = $groqModel;

                Log::channel('webscraper')->info('ClientSiteQa: Using Groq for answer generation', [
                    'base_uri' => $httpBaseUri,
                    'model' => $httpModel,
                ]);
            } elseif ($vllmBaseUri !== '') {
                // Use vLLM OpenAI-compatible endpoint via HTTP
                $useHttp = true;
                $httpBaseUri = rtrim($vllmBaseUri, '/');
                $httpApiKey = $vllmApiKey;
                $httpModel = $vllmModel;

                Log::channel('webscraper')->info('ClientSiteQa: Using vLLM for answer generation', [
                    'base_uri' => $httpBaseUri,
                    'model' => $httpModel,
                ]);
            } else {
                // Fallback to OpenAI
                $apiKey = config('services.openai.key');
                $client = \OpenAI::client($apiKey);
                $vllmModel = $this->llmModel;

                Log::channel('webscraper')->info('ClientSiteQa: Using OpenAI for answer generation', [
                    'model' => $vllmModel,
                ]);
            }

            if ($useHttp) {
                // Use HTTP directly for Groq/vLLM
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $httpApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(60)->post($httpBaseUri . '/chat/completions', [
                    'model' => $httpModel,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $query],
                    ],
                    'temperature' => $this->llmTemperature,
                    'max_tokens' => $this->llmMaxTokens,
                ]);

                if (!$response->successful()) {
                    throw new Exception('API error: ' . $response->body());
                }

                $result = $response->json();
                $answer = $result['choices'][0]['message']['content'] ?? '';
            } else {
                // Use OpenAI client
                $response = $client->chat()->create([
                    'model' => $vllmModel,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $query],
                    ],
                    'temperature' => $this->llmTemperature,
                    'max_tokens' => $this->llmMaxTokens,
                ]);

                $answer = $response->choices[0]->message->content ?? '';
            }

            Log::channel('webscraper')->info('ClientSiteQa: LLM answer generated', [
                'query' => $query,
                'answer_length' => strlen($answer),
                'model' => $vllmModel,
            ]);

            return trim($answer);

        } catch (Exception $e) {
            Log::channel('webscraper')->error('ClientSiteQa: LLM generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Errore durante la generazione della risposta: ' . $e->getMessage());
        }
    }

    /**
     * Extract sources from chunks
     *
     * @param array $similarChunks Array of chunks with scores
     * @return array Array of unique sources
     */
    protected function extractSources(array $similarChunks): array
    {
        $sources = [];
        $seenUrls = [];

        foreach ($similarChunks as $item) {
            $chunk = $item['chunk'];
            if (!$chunk) {
                continue;
            }

            $page = $chunk->page;
            $url = $page->url ?? '';

            // Avoid duplicate sources
            if (!in_array($url, $seenUrls)) {
                $sources[] = [
                    'url' => $url,
                    'title' => $page->title ?? 'No title',
                    'domain' => $page->domain ?? 'Unknown',
                    'score' => $item['score'],
                ];
                $seenUrls[] = $url;
            }
        }

        return $sources;
    }

    /**
     * Public method to perform vector search and return formatted chunks
     * Used by HybridSearchService for combining with text search
     *
     * @param string $query Search query
     * @param string|null $domain Optional domain filter
     * @param int|null $topK Number of results (overrides default)
     * @return array Array of formatted chunks with scores
     */
    public function searchChunks(string $query, ?string $domain = null, ?int $topK = null): array
    {
        // Override topK if provided
        $originalTopK = $this->topK;
        if ($topK !== null) {
            $this->topK = $topK;
        }

        try {
            // Enhance query using strategy pattern (if set)
            $enhancedQuery = $this->enhanceQuery($query, ['domain' => $domain]);

            // Generate query embedding with enhanced query
            $queryEmbedding = $this->embeddingService->generateEmbedding($enhancedQuery);

            // Perform vector search
            $similarChunks = $this->vectorSearch($queryEmbedding, $domain);

            // Format results for hybrid search
            $formattedChunks = [];
            foreach ($similarChunks as $item) {
                $chunk = $item['chunk'];
                $score = $item['score'];

                if (!$chunk) {
                    continue;
                }

                // Load page relationship
                $page = $chunk->page;

                $formattedChunks[] = [
                    'content' => $chunk->content,
                    'score' => $score,
                    'url' => $page->url ?? null,
                    'title' => $page->title ?? 'No title',
                    'description' => $page->description ?? '',
                    'domain' => $chunk->domain ?? $page->domain ?? null,
                    'chunk_index' => $chunk->chunk_index,
                    'word_count' => $chunk->word_count,
                ];
            }

            return $formattedChunks;

        } finally {
            // Restore original topK
            $this->topK = $originalTopK;
        }
    }

    /**
     * Set top K parameter
     *
     * @param int $topK Number of chunks to retrieve
     * @return self
     */
    public function setTopK(int $topK): self
    {
        $this->topK = $topK;
        return $this;
    }

    /**
     * Set minimum similarity threshold
     *
     * @param float $minSimilarity Minimum similarity score (0-1)
     * @return self
     */
    public function setMinSimilarity(float $minSimilarity): self
    {
        $this->minSimilarity = $minSimilarity;
        return $this;
    }
}
