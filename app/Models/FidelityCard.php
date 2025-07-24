<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FidelityCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'card_number',
        'status',
        'points',
        'expires_at',
    ];

    /**
     * Get the customer that owns the fidelity card.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
