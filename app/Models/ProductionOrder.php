<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer',
        'order_date',
        'status',
        'bom_id',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'status' => OrderStatus::class,
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProductionPhase::class);
    }
}
