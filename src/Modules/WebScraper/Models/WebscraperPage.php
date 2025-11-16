<?php

namespace Modules\WebScraper\Models;

 use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * WebscraperPage Model (MongoDB)
 * Represents an indexed web page in the RAG system
 */
class WebscraperPage extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'webscraper_pages';

    protected $fillable = [
        'url',
        'url_hash',
        'domain',
        'title',
        'description',
        'content',
        'raw_html',
        'metadata',
        'word_count',
        'chunk_count',
        'status',
        'error_message',
        'indexed_at',
        'last_scraped_at',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'indexed_at' => 'datetime',
        'last_scraped_at' => 'datetime',
        'expires_at' => 'datetime',
        'word_count' => 'integer',
        'chunk_count' => 'integer',
    ];

    /**
     * Relationship: A page has many chunks
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(WebscraperChunk::class, 'page_id');
    }

    /**
     * Generate URL hash (SHA256)
     */
    public static function generateUrlHash(string $url): string
    {
        return hash('sha256', $url);
    }

    /**
     * Extract domain from URL
     */
    public static function extractDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? '';
    }

    /**
     * Check if page is expired and needs re-indexing
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    /**
     * Mark page as indexed
     */
    public function markAsIndexed(): void
    {
        $this->update([
            'status' => 'indexed',
            'indexed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark page as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Scope: Get only indexed pages
     */
    public function scopeIndexed($query)
    {
        return $query->where('status', 'indexed');
    }

    /**
     * Scope: Get expired pages that need re-indexing
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: Get pages by domain
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }
}