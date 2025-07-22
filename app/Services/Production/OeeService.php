<?php

namespace App\Services\Production;

use App\Models\Workstation;
use Carbon\Carbon;
use App\Models\ProductionPhase;

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

        // 1. Availability: (Tempo di funzionamento effettivo / Tempo pianificato)
        $totalPlannedMinutes = $workstation->availabilities()
            ->get()
            ->sum(function ($availability) use ($startDate, $endDate) {
                return Carbon::parse($availability->start_time)->diffInMinutes(Carbon::parse($availability->end_time)) * $startDate->diffInDaysFiltered(fn (Carbon $date) => $date->isWeekday(), $endDate);
            });

        $actualRunTimeMinutes = ProductionPhase::where('workstation_id', $workstation->id)
            ->whereBetween('end_time', [$startDate, $endDate])
            ->sum('estimated_duration'); // Usiamo la durata stimata come approssimazione del tempo di ciclo

        $availability = ($totalPlannedMinutes > 0) ? ($actualRunTimeMinutes / $totalPlannedMinutes) : 0;

        // 2. Performance: (Produzione effettiva / Produzione teorica nel tempo di funzionamento)
        $totalUnitsProduced = ProductionPhase::where('workstation_id', $workstation->id)
            ->whereBetween('end_time', [$startDate, $endDate])
            ->with('productionOrder')
            ->get()
            ->sum('productionOrder.quantity'); // Questo è un'approssimazione, sarebbe meglio avere pezzi/fase
            
        $idealCycleTimeMinutes = $workstation->time_per_unit;
        $theoreticalProduction = ($idealCycleTimeMinutes > 0) ? ($actualRunTimeMinutes / $idealCycleTimeMinutes) : 0;
        
        $performance = ($theoreticalProduction > 0) ? ($totalUnitsProduced / $theoreticalProduction) : 0;

        // 3. Quality: (Pezzi buoni / Pezzi totali)
        // Simplificato: usiamo l'error_rate della workstation
        $quality = 1 - ($workstation->error_rate / 100);

        $oee = $availability * $performance * $quality;

        return [
            'availability' => min(1, round($availability, 4)),
            'performance' => min(1, round($performance, 4)),
            'quality' => round($quality, 4),
            'oee' => min(1, round($oee, 4)),
        ];
    }
} 