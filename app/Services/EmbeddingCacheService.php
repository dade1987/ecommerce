<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use OpenAI\Client as OpenAIClient;

class EmbeddingCacheService
{
    private OpenAIClient $client;
    private const CACHE_TTL = 86400; // 24 ore in secondi
    private const CACHE_PREFIX = 'embeddings:';

    public function __construct(OpenAIClient $client)
    {
        $this->client = $client;
    }

    /**
     * Ottiene embedding con cache Redis
     * Se disponibile in cache, lo restituisce, altrimenti chiama OpenAI e lo cachea
     */
    public function getEmbedding(string $text, string $model = 'text-embedding-3-small'): ?array
    {
        if (!$text || trim($text) === '') {
            return null;
        }

        $cacheKey = self::CACHE_PREFIX . md5($model . ':' . $text);

        // Prova a prendere dalla cache
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = $this->client->embeddings()->create([
                'model' => $model,
                'input' => $text,
            ]);

            $embedding = $response->data[0]->embedding ?? null;
            if ($embedding) {
                // Cachea per 24 ore
                Cache::put($cacheKey, $embedding, self::CACHE_TTL);
                return $embedding;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('EmbeddingCacheService: Errore nel recupero embedding', [
                'error' => $e->getMessage(),
                'text' => mb_substr($text, 0, 100),
            ]);
        }

        return null;
    }

    /**
     * Calcola similarità coseno tra due vettori
     */
    public static function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Calcola similarità tra due testi usando embeddings cachati
     */
    public function textSimilarity(string $textA, string $textB, string $model = 'text-embedding-3-small'): ?float
    {
        $vecA = $this->getEmbedding($textA, $model);
        $vecB = $this->getEmbedding($textB, $model);

        if (!$vecA || !$vecB) {
            return null;
        }

        return self::cosineSimilarity($vecA, $vecB);
    }

    /**
     * Pulisce cache degli embeddings (utile per testing o manutenzione)
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . '*');
    }
}
