<?php

namespace App\Services\Production;

use App\Models\ProductionOrder;
use App\Models\Workstation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdvancedSchedulingService
{
    /**
     * Generates a detailed production schedule.
     * Can operate on a specific collection of orders for simulation purposes.
     *
     * @param Collection|null $ordersToSchedule
     * @return array
     */
    public function generateSchedule(Collection $ordersToSchedule = null): array
    {
        $log = [];
        $isSimulation = $ordersToSchedule !== null;

        // 1. Carica tutte le fasi di manutenzione esistenti come blocchi iniziali
        $maintenancePhases = \App\Models\ProductionPhase::where('is_maintenance', true)
            ->whereNotNull('scheduled_start_time')
            ->whereNotNull('scheduled_end_time')
            ->get();

        $scheduledPhasesData = $maintenancePhases->map(function ($phase) {
            return [
                'name' => $phase->name,
                'workstation_name' => $phase->workstation->name ?? 'N/D',
                'workstation_id' => $phase->workstation_id,
                'scheduled_start_time' => $phase->scheduled_start_time->toDateTimeString(),
                'scheduled_end_time' => $phase->scheduled_end_time->toDateTimeString(),
                'color' => '#A0AEC0', // Grigio per manutenzione
            ];
        })->toArray();
        $log[] = "Caricati " . count($scheduledPhasesData) . " blocchi di manutenzione pre-esistenti.";

        // 2. Recupera gli ordini da schedulare se non forniti
        if (!$isSimulation) {
            $log[] = "Recupero ordini reali dal database...";
            $ordersToSchedule = ProductionOrder::where('status', 'in_attesa')
                ->orderBy('priority', 'desc')
                ->with('bom', 'phases.workstation')
                ->get();
        } else {
            $log[] = "Utilizzo della collezione di ordini fornita per la simulazione.";
        }

        // 3. Itera e schedula ogni fase di ogni ordine
        $lastPhaseEndTimeByWorkstation = [];

        foreach ($ordersToSchedule as $order) {
            foreach ($order->phases as $phase) {
                 if (!$phase->workstation) {
                    $log[] = "Skipping phase {$phase->name} (ID: {$phase->id}) - no workstation assigned.";
                    continue;
                }
                
                $workstation = $phase->workstation;
                
                // La durata ora dipende dalla quantità dell'ordine
                $processingTime = ($order->quantity ?? 1) * ($phase->estimated_duration ?? 60);
                $totalDuration = $processingTime + ($phase->setup_time ?? 0);

                // Trova il prossimo slot disponibile per questa postazione
                $lastEndTime = $lastPhaseEndTimeByWorkstation[$workstation->id] ?? now();
                $slot = $this->findNextAvailableSlot($workstation, $lastEndTime, $totalDuration, $scheduledPhasesData);

                // Salva le date se NON è una simulazione
                if (!$isSimulation) {
                    $phase->scheduled_start_time = $slot['start'];
                    $phase->scheduled_end_time = $slot['end'];
                    $phase->save();
                }

                // Aggiungi sempre i dati al nostro array per il Gantt
                $scheduledPhasesData[] = [
                    'name' => "Ord. {$order->id}: {$phase->name}",
                    'workstation_name' => $workstation->name,
                    'workstation_id' => $workstation->id,
                    'scheduled_start_time' => $slot['start']->toDateTimeString(),
                    'scheduled_end_time' => $slot['end']->toDateTimeString(),
                    'color' => '#4299e1', // Blu per produzione
                ];
                
                // Aggiorna l'ultimo orario di fine per questa specifica postazione
                $lastPhaseEndTimeByWorkstation[$workstation->id] = $slot['end'];
            }
        }
        
        return [
            'log' => $log,
            'scheduled_phases_data' => $scheduledPhasesData,
        ];
    }

    /**
     * Finds the next available time slot for a given phase on a workstation.
     *
     * @param Workstation $workstation
     * @param Carbon $earliestStartTime
     * @param int $durationMinutes
     * @param array $allScheduledPhases
     * @param bool $isMaintenance
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    private function findNextAvailableSlot(Workstation $workstation, Carbon $earliestStartTime, int $durationMinutes, array &$allScheduledPhases, bool $isMaintenance = false): array
    {
        $checkTime = $earliestStartTime->copy();

        while (true) {
            // Usa dayOfWeekIso (1=Lunedì, 7=Domenica) per coerenza con lo standard DB
            $currentDayOfWeek = $checkTime->dayOfWeekIso;

            // Cerca il prossimo slot di tempo lavorativo valido
            $availability = $workstation->availabilities()
                ->where('day_of_week', $currentDayOfWeek)
                ->first();

            if (!$availability) {
                // Se oggi non c'è disponibilità (es. weekend), salta all'inizio del giorno dopo.
                $checkTime->addDay()->startOfDay();
                continue;
            }
            
            $workingStart = Carbon::parse($checkTime->format('Y-m-d') . ' ' . $availability->start_time);
            $workingEnd = Carbon::parse($checkTime->format('Y-m-d') . ' ' . $availability->end_time);

            // Se siamo prima dell'orario di inizio lavoro, spostiamoci all'inizio.
            if ($checkTime < $workingStart) {
                $checkTime = $workingStart;
            }

            // Se abbiamo superato l'orario di fine lavoro, passiamo al giorno dopo.
            if ($checkTime >= $workingEnd) {
                $checkTime->addDay()->startOfDay();
                continue;
            }
            
            // Controlla se la fase può essere completata entro l'orario di lavoro
            $proposedEndTime = $checkTime->copy()->addMinutes($durationMinutes);
            if ($proposedEndTime > $workingEnd) {
                // Non c'è abbastanza tempo oggi, passa al giorno dopo.
                $checkTime->addDay()->startOfDay();
                continue;
            }

            // Controlla la sovrapposizione con altre fasi già schedulate
            $isOverlapping = false;
            foreach ($allScheduledPhases as $scheduledPhase) {
                if ($scheduledPhase['workstation_id'] === $workstation->id) {
                    $existingStart = Carbon::parse($scheduledPhase['scheduled_start_time']);
                    $existingEnd = Carbon::parse($scheduledPhase['scheduled_end_time']);

                    if ($checkTime < $existingEnd && $proposedEndTime > $existingStart) {
                        $isOverlapping = true;
                        $checkTime = $existingEnd; // Salta alla fine della fase che crea conflitto
                        break;
                    }
                }
            }

            if (!$isOverlapping) {
                return ['start' => $checkTime, 'end' => $proposedEndTime];
            }
        }
    }

    /**
     * Verifica se un orario è all'interno di un range.
     *
     * @param Carbon $time
     * @param string $startTime
     * @param string $endTime
     * @return bool
     */
    private function isWithinTimeRange(Carbon $time, string $startTime, string $endTime): bool
    {
        $start = Carbon::createFromTimeString($startTime);
        $end = Carbon::createFromTimeString($endTime);
        return $time->format('H:i:s') >= $start->format('H:i:s') && $time->format('H:i:s') <= $end->format('H:i:s');
    }
} 