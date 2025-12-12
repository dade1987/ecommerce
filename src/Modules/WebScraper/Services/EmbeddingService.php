<?php

namespace Modules\WebScraper\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;

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
     * Uses cache to avoid regenerating embeddings for identical or similar queries
     *
     * @param string $text Text to embed
     * @return array|null Embedding vector (1536 dimensions) or null on error
     */
    public function generateEmbedding(string $text): ?array
    {
        try {
            return $this->generateEmbeddingPromise($text)->wait();

        } catch (\Exception $e) {
            Log::channel('webscraper')->error('EmbeddingService: Error generating embedding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Generate embedding asynchronously (Promise).
     * This enables overlapping the embedding HTTP call with other work (e.g., Mongo text search).
     *
     * @param string $text Text to embed
     * @return PromiseInterface Promise resolving to embedding array
     */
    public function generateEmbeddingPromise(string $text): PromiseInterface
    {
        // Normalize text for cache key (trim, remove extra spaces)
        $normalizedText = $this->normalizeTextForCache($text);
        $cacheKey = $this->getCacheKey($normalizedText);

        // Cache hit: return fulfilled promise
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::channel('webscraper')->debug('EmbeddingService: Cache hit', [
                'text' => substr($text, 0, 100),
                'model' => $this->model,
                'dimensions' => is_array($cached) ? count($cached) : null,
            ]);
            return Create::promiseFor($cached);
        }

        $apiKey = (string) config('services.openai.key');
        if ($apiKey === '') {
            return Create::rejectionFor(new \RuntimeException('Missing OpenAI API key (services.openai.key)'));
        }

        Log::channel('webscraper')->debug('EmbeddingService: Generating embedding (async)', [
            'text' => substr($text, 0, 100),
            'model' => $this->model,
            'cache_key' => $cacheKey,
        ]);

        $client = new GuzzleClient([
            'base_uri' => 'https://api.openai.com/v1/',
            'timeout' => 60,
        ]);

        $ttl = config('webscraper.rag.embedding.cache_ttl', 60 * 60 * 24 * 7); // 7 days default

        return $client->postAsync('embeddings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $this->model,
                'input' => $text,
            ],
        ])->then(
            function ($response) use ($cacheKey, $ttl) {
                $body = (string) $response->getBody();
                $json = json_decode($body, true);

                $embedding = $json['data'][0]['embedding'] ?? null;
                if (!is_array($embedding)) {
                    Log::channel('webscraper')->error('EmbeddingService: No embedding in async response', [
                        'response_body_preview' => substr($body, 0, 300),
                    ]);
                    throw new \RuntimeException('No embedding in response');
                }

                Cache::put($cacheKey, $embedding, $ttl);

                Log::channel('webscraper')->debug('EmbeddingService: Embedding generated and cached (async)', [
                    'dimensions' => count($embedding),
                    'cache_ttl_seconds' => $ttl,
                ]);

                return $embedding;
            }
        );
    }

    /**
     * Normalize text for cache key generation
     * Removes extra spaces, trims, and normalizes whitespace
     *
     * @param string $text Original text
     * @return string Normalized text
     */
    protected function normalizeTextForCache(string $text): string
    {
        // Trim and normalize whitespace
        $normalized = trim($text);
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        return $normalized;
    }

    /**
     * Generate cache key for embedding
     *
     * @param string $normalizedText Normalized text
     * @return string Cache key
     */
    protected function getCacheKey(string $normalizedText): string
    {
        // Include model name in cache key to avoid conflicts
        $hash = hash('sha256', $normalizedText . '|' . $this->model);
        return "embedding:{$this->model}:{$hash}";
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