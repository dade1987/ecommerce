<?php

namespace App\Services\Production;

use App\Models\OperatorFeedback;
use App\Models\ProductionPhase;
use App\Models\Workstation;
use Carbon\Carbon;
use function Safe\json_decode;

class OeeService
{
    /**
     * Calcola l'OEE (Overall Equipment Effectiveness) per una data postazione di lavoro.
     * OEE = Disponibilità * Performance * Qualità
     *
     * @param Workstation $workstation
     * @return array
     */
    public function calculateForWorkstation(Workstation $workstation, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subMonth();
        $endDate = $endDate ?? now();

        $phases = ProductionPhase::where('workstation_id', $workstation->id)
            ->whereBetween('end_time', [$startDate, $endDate])
            ->get();

        if ($phases->isEmpty()) {
            return [
                'availability' => 0,
                'performance' => 0,
                'quality' => 0,
                'oee' => 0,
            ];
        }

        // 1. Availability
        $totalPlannedMinutes = $workstation->getPlannedProductiveMinutes($startDate, $endDate);

        $actualRunTimeMinutes = $phases->sum(function ($phase) {
            if ($phase->start_time && $phase->end_time) {
                return Carbon::parse($phase->start_time)->diffInMinutes(Carbon::parse($phase->end_time));
            }

            return 0;
        });

        $availability = ($totalPlannedMinutes > 0) ? ($actualRunTimeMinutes / $totalPlannedMinutes) : 0;

        // 2. Performance & 3. Quality
        $totalUnitsProduced = 0;
        $totalScrap = 0;

        $phaseIds = $phases->pluck('id')->toArray();
        $feedbacks = OperatorFeedback::whereBetween('created_at', [$startDate, $endDate])->get();
        foreach ($feedbacks as $feedback) {
            $meta = json_decode($feedback->metadata, true);
            if (isset($meta['production_phase_id']) && in_array($meta['production_phase_id'], $phaseIds)) {
                $totalUnitsProduced += $meta['produced_quantity'] ?? 0;
                $totalScrap += $meta['scrap_quantity'] ?? 0;
            }
        }
        $totalProcessed = $totalUnitsProduced + $totalScrap;

        $idealCycleTimeMinutes = $workstation->time_per_unit;

        if ($idealCycleTimeMinutes > 0 && $actualRunTimeMinutes > 0) {
            $theoreticalProduction = $actualRunTimeMinutes / $idealCycleTimeMinutes;
            $performance = ($theoreticalProduction > 0) ? ($totalProcessed / $theoreticalProduction) : 0;
        } else {
            $performance = 0;
        }

        $quality = ($totalProcessed > 0) ? ($totalUnitsProduced / $totalProcessed) : 0;

        $oee = $availability * $performance * $quality;

        return [
            'availability' => min(1, round($availability, 4)),
            'performance' => min(1, round($performance, 4)),
            'quality' => round($quality, 4),
            'oee' => min(1, round($oee, 4)),
        ];
    }
}
