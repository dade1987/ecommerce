<?php

namespace App\Services\Production;

use App\Models\Workstation;

class OeeService
{
    /**
     * Calcola l'OEE (Overall Equipment Effectiveness) per una data postazione di lavoro.
     * OEE = Disponibilità * Performance * Qualità
     *
     * @param Workstation $workstation
     * @return array
     */
    public function calculateForWorkstation(Workstation $workstation): array
    {
        // 1. Calcolo della Disponibilità (simplificato)
        // Si basa sullo stato in tempo reale. In un sistema reale, si userebbero i log di uptime/downtime.
        $availability = match ($workstation->real_time_status) {
            'running' => 0.95, // Si assume un 5% di micro-fermate, cambi, ecc.
            'idle' => 1.0,     // Disponibile ma non in uso
            'faulted' => 0.1,  // Fortemente indisponibile
            default => 0.0,
        };

        // 2. Calcolo della Performance (simplificato)
        // Confronta la velocità attuale con la velocità teorica ideale.
        $idealSpeed = ($workstation->time_per_unit > 0) ? (60 / $workstation->time_per_unit) : 0; // unità/ora
        $currentSpeed = $workstation->current_speed ?? 0;
        $performance = ($idealSpeed > 0) ? ($currentSpeed / $idealSpeed) : 0;
        $performance = min($performance, 1.0); // La performance non può superare il 100%

        // 3. Calcolo della Qualità
        // Basato direttamente sul tasso di errore registrato.
        $quality = 1 - ($workstation->error_rate / 100);

        // Calcolo dell'OEE complessivo
        $oee = $availability * $performance * $quality;

        return [
            'availability' => round($availability, 4),
            'performance' => round($performance, 4),
            'quality' => round($quality, 4),
            'oee' => round($oee, 4),
        ];
    }
} 