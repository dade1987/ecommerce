<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'workstation_id',
        'name',
        'estimated_duration',
        'setup_time',
        'start_time',
        'end_time',
        'scheduled_start_time',
        'scheduled_end_time',
        'operator',
        'is_completed',
        'is_maintenance',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
        'is_completed' => 'boolean',
        'is_maintenance' => 'boolean',
    ];

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class);
    }
}
