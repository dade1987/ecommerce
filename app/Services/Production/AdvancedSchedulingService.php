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
    public function generateSchedule(): array
    {
        // Ottieni tutti gli ordini che non sono ancora completati
        $orders = ProductionOrder::where('status', 'in_attesa')
            ->orderBy('priority', 'desc')
            ->orderBy('order_date', 'asc')
            ->with(['phases' => function ($query) {
                $query->where('is_completed', false);
            }, 'phases.workstation.availabilities'])
            ->get();

        $log = [];
        $isSimulation = false; // This variable is no longer needed as $ordersToSchedule is removed

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
            // The original code had $ordersToSchedule = ProductionOrder::where('status', 'in_attesa')
            // This line is removed as $ordersToSchedule is no longer available.
            // The new code directly uses $orders.
        } else {
            $log[] = "Utilizzo della collezione di ordini fornita per la simulazione.";
        }

        // 3. Itera e schedula ogni fase di ogni ordine
        $lastPhaseEndTimeByWorkstation = [];

        foreach ($orders as $order) {
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

                if (empty($slot['start']) || empty($slot['end'])) {
                    $log[] = "Impossibile schedulare la fase '{$phase->name}' (Ordine {$order->id}) su {$workstation->name}: nessuno slot disponibile.";
                    $scheduledPhasesData[] = [
                        'name' => "Ord. {$order->id}: {$phase->name}",
                        'workstation_name' => $workstation->name,
                        'workstation_id' => $workstation->id,
                        'scheduled_start_time' => null,
                        'scheduled_end_time' => null,
                        'color' => '#e53e3e', // Rosso per errore
                        'error' => $slot['error'] ?? 'Nessuno slot disponibile',
                    ];
                    continue;
                }


                // Salva le date se NON è una simulazione
                if (!$isSimulation) {
                    $phase->scheduled_start_time = $slot['start'] ?? null;
                    $phase->scheduled_end_time = $slot['end'] ?? null;
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
    private function findNextAvailableSlot(Workstation $workstation, Carbon $earliestStartTime, int $durationMinutes, array &$allScheduledPhases): array
    {
        $checkTime = $earliestStartTime->copy();
        $iteration = 0;
        $maxIterations = 365; // max 1 anno di tentativi

        while (true) {
            $iteration++;
            if ($iteration > $maxIterations) {
                // Nessuno slot trovato in un anno: esci e segnala errore
                return [
                    'start' => null,
                    'end' => null,
                    'error' => 'Nessuno slot disponibile per questa fase in 1 anno',
                ]; 
            }

            $currentDayOfWeek = $checkTime->dayOfWeekIso; // 1=Lunedì, 7=Domenica

            $availability = $workstation->availabilities()->where('day_of_week', $currentDayOfWeek)->first();

            if (!$availability) {
                // Debug: nessuna disponibilità trovata per questo giorno
                // dd('Nessuna disponibilità trovata', $workstation->id, $currentDayOfWeek);
                $checkTime->addDay()->startOfDay();
                continue;
            }

            $workingStart = Carbon::parse($checkTime->format('Y-m-d') . ' ' . $availability->start_time);
            $workingEnd = Carbon::parse($checkTime->format('Y-m-d') . ' ' . $availability->end_time);

            if ($checkTime < $workingStart) $checkTime = $workingStart;
            if ($checkTime >= $workingEnd) {
                $checkTime->addDay()->startOfDay();
                continue;
            }

            $proposedEndTime = $checkTime->copy()->addMinutes($durationMinutes);
            if ($proposedEndTime > $workingEnd) {
                $checkTime->addDay()->startOfDay();
                continue;
            }

            // Ordina le fasi schedulate per ottimizzare la ricerca
            usort($allScheduledPhases, fn($a, $b) => strcmp($a['scheduled_start_time'], $b['scheduled_start_time']));

            $conflict = false;
            foreach ($allScheduledPhases as $phase) {
                if ($phase['workstation_id'] !== $workstation->id) continue;

                $existingStart = Carbon::parse($phase['scheduled_start_time']);
                $existingEnd = Carbon::parse($phase['scheduled_end_time']);

                if ($checkTime < $existingEnd && $proposedEndTime > $existingStart) {
                    $checkTime = $existingEnd; // Salta alla fine del conflitto
                    $conflict = true;
                    break; 
                }
            }

            if ($conflict) {
                continue; // Riavvia il ciclo while con il nuovo checkTime
            }

            // Debug: slot trovato
            // dd('Slot trovato', [
            //     'workstation_id' => $workstation->id,
            //     'checkTime' => $checkTime,
            //     'proposedEndTime' => $proposedEndTime,
            // ]);

            return ['start' => $checkTime, 'end' => $proposedEndTime];
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