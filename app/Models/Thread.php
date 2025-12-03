<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class Thread extends Model
{
    use HasFactory;

    protected $primaryKey = 'thread_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'thread_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'team_slug',
        'activity_uuid',
        'cookies',
        'headers',
        'server_params',
        'is_fake',
    ];

    protected $casts = [
        'cookies' => 'array',
        'headers' => 'array',
        'server_params' => 'array',
        'is_fake' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Quoter::class, 'thread_id', 'thread_id');
    }

    /**
     * Crea (solo se non esiste) un record Thread catturando le informazioni
     * principali dalla Request e da eventuali extra (es. team_slug, activity_uuid).
     */
    public static function captureFromRequest(string $threadId, Request $request, array $extra = []): self
    {
        $thread = static::firstOrNew(['thread_id' => $threadId]);

        if ($thread->exists) {
            return $thread;
        }

        $server = is_array($_SERVER) ? $_SERVER : [];

        // Escludi potenziali chiavi sensibili
        $server = Arr::except($server, [
            'PHP_AUTH_PW',
            'HTTP_AUTHORIZATION',
            'DB_PASSWORD',
            'DB_USERNAME',
            'APP_KEY',
        ]);

        $thread->ip_address = $request->ip();
        $thread->user_agent = (string) $request->header('User-Agent', '');
        $thread->url = (string) $request->fullUrl();
        $thread->method = (string) $request->method();

        $thread->team_slug = $extra['team_slug'] ?? null;
        $thread->activity_uuid = $extra['activity_uuid'] ?? null;

        $thread->cookies = $request->cookies->all();
        $thread->headers = $request->headers->all();
        $thread->server_params = $server;

        $thread->save();

        return $thread;
    }
}
