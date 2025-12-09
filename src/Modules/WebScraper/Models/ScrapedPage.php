<?php

namespace Modules\WebScraper\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ScrapedPage extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'webscraper';

    /**
     * The table associated with the model.
     */
    protected $table = 'scraped_pages';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'url',
        'url_hash',
        'scraped_data',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scraped_data' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Dominio estratto dall'URL (es. example.com).
     */
    public function getDomainAttribute(): ?string
    {
        $url = (string) $this->url;

        if ($url === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host ?: null;
    }

    /**
     * Boot the model and ensure storage directory exists
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure storage directory exists
        $dbPath = storage_path('webscraper');
        if (!file_exists($dbPath)) {
            mkdir($dbPath, 0755, true);
        }
    }

    /**
     * Get cached data for a URL (if not expired)
     */
    public static function getCached(string $url): ?array
    {
        $hash = md5($url);

        $cached = self::where('url_hash', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if ($cached) {
            return $cached->scraped_data;
        }

        return null;
    }

    /**
     * Store scraped data in cache
     */
    public static function storeCache(string $url, array $data, int $ttl = 86400): void
    {
        $hash = md5($url);

        self::updateOrCreate(
            ['url_hash' => $hash],
            [
                'url' => $url,
                'scraped_data' => $data,
                'expires_at' => Carbon::now()->addSeconds($ttl),
            ]
        );
    }

    /**
     * Clear expired cache entries
     */
    public static function clearExpired(): int
    {
        return self::where('expires_at', '<=', now())->delete();
    }

    /**
     * Clear cache for a specific URL
     */
    public static function clearUrl(string $url): bool
    {
        $hash = md5($url);
        return (bool) self::where('url_hash', $hash)->delete();
    }

    /**
     * Clear all cache
     */
    public static function clearAll(): int
    {
        return self::query()->delete();
    }

    /**
     * Get cache statistics
     */
    public static function getStats(): array
    {
        $total = self::count();
        $expired = self::where('expires_at', '<=', now())->count();
        $valid = $total - $expired;

        return [
            'total_entries' => $total,
            'valid_entries' => $valid,
            'expired_entries' => $expired,
            'database_size' => self::getDatabaseSize(),
        ];
    }

    /**
     * Get database file size in bytes
     */
    protected static function getDatabaseSize(): int
    {
        $dbPath = storage_path('webscraper/webscraper.sqlite');
        return file_exists($dbPath) ? filesize($dbPath) : 0;
    }
}