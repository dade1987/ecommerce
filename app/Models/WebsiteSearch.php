<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'website',
        'query',
        'team_id',
        'locale',
        'response',
        'content_length',
        'from_cache',
    ];

    protected $casts = [
        'from_cache' => 'boolean',
        'content_length' => 'integer',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
