<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workstation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'production_line_id',
        'status',
        'capacity',
        'real_time_status',
        'wear_level',
        'last_maintenance_date',
        'error_rate',
        'current_speed',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(WorkstationAvailability::class);
    }
}
