<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'internal_code',
        'materials',
        'internal_product_id',
    ];

    protected $casts = [
        'materials' => 'array',
    ];

    public function internalProduct(): BelongsTo
    {
        return $this->belongsTo(InternalProduct::class);
    }
}
