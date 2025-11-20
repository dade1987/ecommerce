<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    /**
     * Relazione con ProductionOrder
     * @return HasMany<ProductionOrder>
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    /**
     * Relazione con ProductionPhase tramite ProductionOrder
     * @return HasManyThrough<ProductionPhase>
     */
    public function phases(): HasManyThrough
    {
        return $this->hasManyThrough(ProductionPhase::class, ProductionOrder::class);
    }
}
