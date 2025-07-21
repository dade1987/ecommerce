<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkstationAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'workstation_id',
        'day_of_week',
        'exception_date',
        'start_time',
        'end_time',
        'is_available',
        'type',
    ];

    protected $casts = [
        'exception_date' => 'date',
        'is_available' => 'boolean',
    ];

    public function workstation(): BelongsTo
    {
        return $this->belongsTo(Workstation::class);
    }
}
