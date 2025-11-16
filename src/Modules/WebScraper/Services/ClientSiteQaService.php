<?php

namespace Modules\WebScraper\Services;

use Modules\WebScraper\Models\WebscraperChunk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
    protected EmbeddingService $embeddingService;

    // Vector search configuration
    protected int $topK = 10; // Number of similar chunks to retrieve (reduced to fit GPT-3.5-turbo 16K context)
    protected float $minSimilarity = 0.7; // Minimum similarity score (0-1)

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
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
        // MongoDB Atlas Vector Search aggregation pipeline
        $pipeline = [
            [
                '$vectorSearch' => [
                    'index' => 'vector_index_1', // Name of the Atlas Vector Search index
                    'path' => 'embedding',
                    'queryVector' => $queryEmbedding,
                    'numCandidates' => 100, // Number of candidates to consider
                    'limit' => $this->topK,
                ]
            ],
            [
                '$addFields' => [
                    'score' => ['$meta' => 'vectorSearchScore']
                ]
            ],
        ];

        // Domain filter (direct field, no $lookup needed - much faster!)
        if ($domain) {
            $pipeline[] = [
                '$match' => [
                    'domain' => $domain
                ]
            ];

            Log::channel('webscraper')->debug('ClientSiteQa: Domain filter applied', [
                'domain' => $domain,
            ]);
        }

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
            // Use OpenAI client directly (same as EmbeddingService)
            $apiKey = config('services.openai.key');
            $client = \OpenAI::client($apiKey);

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $query],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            $answer = $response->choices[0]->message->content ?? '';

            Log::channel('webscraper')->info('ClientSiteQa: LLM answer generated', [
                'query' => $query,
                'answer_length' => strlen($answer),
                'model' => 'gpt-3.5-turbo',
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
