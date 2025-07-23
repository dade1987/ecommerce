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
        'batch_size',
        'time_per_unit',
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

    /**
     * Calcola i minuti produttivi pianificati tra due date, considerando le availabilities settimanali.
     */
    public function getPlannedProductiveMinutes($startDate, $endDate): int
    {
        $totalMinutes = 0;
        $availabilities = $this->availabilities()->where('is_available', true)->get();
        if ($availabilities->isEmpty()) {
            return 0;
        }
        $period = new \DatePeriod(
            new \DateTime($startDate->toDateString()),
            new \DateInterval('P1D'),
            (new \DateTime($endDate->toDateString()))->modify('+1 day')
        );
        foreach ($period as $date) {
            $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, ...
            foreach ($availabilities as $availability) {
                if ($availability->day_of_week === $dayOfWeek) {
                    $start = \Carbon\Carbon::parse($availability->start_time);
                    $end = \Carbon\Carbon::parse($availability->end_time);
                    $minutes = $start->diffInMinutes($end);
                    $totalMinutes += $minutes;
                }
            }
        }
        return $totalMinutes;
    }
}
