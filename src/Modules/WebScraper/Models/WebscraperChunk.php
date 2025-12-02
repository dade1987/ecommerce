<?php

namespace Modules\WebScraper\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WebscraperChunk Model (MongoDB)
 * Represents a chunk of text with its embedding vector for RAG similarity search
 */
class WebscraperChunk extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'webscraper_chunks';

    protected $fillable = [
        'page_id',
        'content',
        'chunk_index',
        'word_count',
        'embedding',
        'chunk_hash',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'chunk_index' => 'integer',
        'word_count' => 'integer',
    ];

    /**
     * Get the embedding attribute as array
     */
    public function getEmbeddingAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    /**
     * Set the embedding attribute - ensure it's stored as proper array for Atlas Vector Search
     */
    public function setEmbeddingAttribute($value)
    {
        // Store as native array (not JSON string) for Atlas Vector Search compatibility
        $this->attributes['embedding'] = $value;
    }

    /**
     * Relationship: A chunk belongs to a page
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(WebscraperPage::class, 'page_id');
    }

    /**
     * Generate chunk hash (SHA256 of content)
     */
    public static function generateChunkHash(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * Calculate cosine similarity between this chunk's embedding and a query embedding
     *
     * @param array $queryEmbedding Array of floats (1536 dimensions)
     * @return float Similarity score between 0 and 1
     */
    public function cosineSimilarity(array $queryEmbedding): float
    {
        if (!$this->embedding || empty($this->embedding)) {
            return 0.0;
        }

        $chunkEmbedding = $this->embedding;

        // Ensure both vectors have the same dimensionality
        if (count($chunkEmbedding) !== count($queryEmbedding)) {
            return 0.0;
        }

        // Calculate dot product and magnitudes
        $dotProduct = 0.0;
        $chunkMagnitude = 0.0;
        $queryMagnitude = 0.0;

        for ($i = 0; $i < count($chunkEmbedding); $i++) {
            $dotProduct += $chunkEmbedding[$i] * $queryEmbedding[$i];
            $chunkMagnitude += $chunkEmbedding[$i] * $chunkEmbedding[$i];
            $queryMagnitude += $queryEmbedding[$i] * $queryEmbedding[$i];
        }

        $chunkMagnitude = sqrt($chunkMagnitude);
        $queryMagnitude = sqrt($queryMagnitude);

        // Avoid division by zero
        if ($chunkMagnitude == 0.0 || $queryMagnitude == 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct / ($chunkMagnitude * $queryMagnitude);

        // Clamp to [0, 1] range (should already be in this range, but just in case)
        return max(0.0, min(1.0, $similarity));
    }

    /**
     * Get full context with page metadata
     */
    public function getFullContext(): array
    {
        return [
            'url' => $this->page->url,
            'title' => $this->page->title,
            'domain' => $this->page->domain,
            'content' => $this->content,
            'chunk_index' => $this->chunk_index,
            'metadata' => array_merge(
                $this->page->metadata ?? [],
                $this->metadata ?? []
            ),
        ];
    }

    /**
     * Scope: Get chunks with embeddings
     */
    public function scopeWithEmbeddings($query)
    {
        return $query->whereNotNull('embedding');
    }

    /**
     * Scope: Get chunks by page
     */
    public function scopeByPage($query, int $pageId)
    {
        return $query->where('page_id', $pageId)->orderBy('chunk_index');
    }
}