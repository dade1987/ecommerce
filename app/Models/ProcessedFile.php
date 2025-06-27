<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedFile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'gemini_response' => 'array',
    ];

    public function extractor(): BelongsTo
    {
        return $this->belongsTo(Extractor::class);
    }
}
