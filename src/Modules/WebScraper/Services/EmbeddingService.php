<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service for generating and comparing text embeddings using OpenAI
 */
class EmbeddingService
{
    /**
     * OpenAI model for embeddings
     */
    protected string $model;

    /**
     * Expected embedding dimensions
     */
    protected int $dimensions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = config('webscraper.rag.embedding.model', 'text-embedding-3-small');
        $this->dimensions = config('webscraper.rag.embedding.dimensions', 1536);
    }

    /**
     * Generate embedding vector for a text query
     *
     * @param string $text Text to embed
     * @return array|null Embedding vector (1536 dimensions) or null on error
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            Log::channel('webscraper')->debug('EmbeddingService: Generating embedding', [
                'text' => substr($text, 0, 100),
                'model' => $this->model,
            ]);

            // Use OpenAI client directly
            $apiKey = config('services.openai.key');
            $client = \OpenAI::client($apiKey);

            $response = $client->embeddings()->create([
                'model' => $this->model,
                'input' => $text,
            ]);

            $embedding = $response->embeddings[0]->embedding ?? null;

            if ($embedding === null) {
                Log::channel('webscraper')->error('EmbeddingService: No embedding in response');
                return null;
            }

            Log::channel('webscraper')->debug('EmbeddingService: Embedding generated', [
                'dimensions' => count($embedding),
                'first_5_values' => array_slice($embedding, 0, 5),
                'last_5_values' => array_slice($embedding, -5),
            ]);

            return $embedding;

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('EmbeddingService: Error generating embedding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Calculate cosine similarity between two embedding vectors
     *
     * @param array $embedding1 First embedding vector
     * @param array $embedding2 Second embedding vector
     * @return float Similarity score (0.0 to 1.0, where 1.0 is identical)
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;

        $count = min(count($embedding1), count($embedding2));

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $magnitude1 += $embedding1[$i] * $embedding1[$i];
            $magnitude2 += $embedding2[$i] * $embedding2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Find the most similar query from a list of candidates
     *
     * @param string $query Query text to compare
     * @param array $candidates Array of ['query' => string, 'embedding' => array] candidates
     * @param float $threshold Minimum similarity threshold (default 0.7)
     * @return array|null ['query' => string, 'similarity' => float] or null if no match above threshold
     */
    public function findMostSimilar(string $query, array $candidates, float $threshold = 0.7): ?array
    {
        $queryEmbedding = $this->generateEmbedding($query);

        if ($queryEmbedding === null) {
            Log::channel('webscraper')->warning('EmbeddingService: Cannot generate embedding for query');
            return null;
        }

        $bestMatch = null;
        $bestSimilarity = 0.0;

        foreach ($candidates as $candidate) {
            if (!isset($candidate['embedding']) || !is_array($candidate['embedding'])) {
                continue;
            }

            $similarity = $this->cosineSimilarity($queryEmbedding, $candidate['embedding']);

            Log::channel('webscraper')->debug('EmbeddingService: Comparing similarity', [
                'candidate_query' => $candidate['query'] ?? 'unknown',
                'similarity' => round($similarity, 4),
            ]);

            if ($similarity > $bestSimilarity && $similarity >= $threshold) {
                $bestSimilarity = $similarity;
                $bestMatch = [
                    'query' => $candidate['query'] ?? '',
                    'similarity' => $similarity,
                    'candidate' => $candidate,
                ];
            }
        }

        if ($bestMatch !== null) {
            Log::channel('webscraper')->info('EmbeddingService: Found similar query', [
                'original_query' => $query,
                'matched_query' => $bestMatch['query'],
                'similarity' => round($bestMatch['similarity'], 4),
            ]);
        } else {
            Log::channel('webscraper')->info('EmbeddingService: No similar query found above threshold', [
                'query' => $query,
                'threshold' => $threshold,
            ]);
        }

        return $bestMatch;
    }

    /**
     * Encode embedding array to JSON string for storage
     *
     * @param array $embedding Embedding vector
     * @return string JSON encoded string
     */
    public function encodeEmbedding(array $embedding): string
    {
        return json_encode($embedding);
    }

    /**
     * Decode embedding from JSON string
     *
     * @param string|null $embeddingJson JSON encoded embedding
     * @return array|null Embedding vector or null
     */
    public function decodeEmbedding(?string $embeddingJson): ?array
    {
        if ($embeddingJson === null) {
            return null;
        }

        $decoded = json_decode($embeddingJson, true);

        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}