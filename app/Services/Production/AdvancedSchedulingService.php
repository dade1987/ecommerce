<?php

namespace App\Services\Production;

use App\Models\ProductionOrder;
use App\Models\Workstation;
use Carbon\Carbon;

class AdvancedSchedulingService
{
    /**
     * Generates a detailed production schedule.
     *
     * @return array
     */
    public function generateSchedule(): array
    {
        $log = [];
        $scheduledPhasesData = []; // Store scheduled phase data for overlap checks

        $orders = ProductionOrder::whereIn('status', ['in_attesa', 'in_produzione'])
            ->orderBy('priority', 'desc')
            ->with(['phases' => function ($query) {
                $query->where('is_completed', false)->orderBy('id');
            }, 'phases.workstation.availabilities'])
            ->get();

        foreach ($orders as $order) {
            $lastPhaseEndTime = now();

            foreach ($order->phases as $phase) {
                if (!$phase->workstation) {
                    $log[] = "Skipping phase {$phase->name} (ID: {$phase->id}) - no workstation assigned.";
                    continue;
                }

                $workstation = $phase->workstation;
                $estimatedDuration = $phase->estimated_duration;

                if (is_null($estimatedDuration)) {
                    $log[] = "Skipping phase {$phase->name} (ID: {$phase->id}) - no estimated duration.";
                    continue;
                }
                
                $totalDuration = $estimatedDuration + ($phase->setup_time ?? 0);

                $slot = $this->findNextAvailableSlot($workstation, $lastPhaseEndTime, $totalDuration, $scheduledPhasesData, $phase->is_maintenance);
                
                if ($phase->is_maintenance) {
                    $log[] = "Scheduling maintenance block on {$workstation->name} from {$slot['start']} to {$slot['end']}.";
                } else {
                    $log[] = "Scheduled phase {$phase->name} (ID: {$phase->id}) on {$workstation->name} from {$slot['start']} to {$slot['end']} (includes setup time).";
                }

                $phase->scheduled_start_time = $slot['start'];
                $phase->scheduled_end_time = $slot['end'];
                $phase->save();

                $scheduledPhasesData[] = [
                    'workstation_id' => $workstation->id,
                    'scheduled_start_time' => $slot['start']->toDateTimeString(),
                    'scheduled_end_time' => $slot['end']->toDateTimeString(),
                ];
                $lastPhaseEndTime = $slot['end'];
            }
        }

        return ['log' => $log];
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
            // Controlla la disponibilità della workstation (orari di lavoro)
            $availability = $workstation->availabilities()
                ->where('day_of_week', $checkTime->dayOfWeek)
                ->first();

            if (!$availability || !$this->isWithinTimeRange($checkTime, $availability->start_time, $availability->end_time)) {
                // Se fuori orario, passa al giorno successivo
                $checkTime->addDay()->startOfDay();
                continue;
            }

            $proposedEndTime = $checkTime->copy()->addMinutes($durationMinutes);

            // Controlla la sovrapposizione con altre fasi già schedulate (inclusa manutenzione)
            $isOverlapping = false;
            foreach ($allScheduledPhases as $scheduledPhase) {
                if ($scheduledPhase['workstation_id'] === $workstation->id) {
                    $existingStart = Carbon::parse($scheduledPhase['scheduled_start_time']);
                    $existingEnd = Carbon::parse($scheduledPhase['scheduled_end_time']);

                    if ($checkTime->between($existingStart, $existingEnd) || $proposedEndTime->between($existingStart, $existingEnd)) {
                        $isOverlapping = true;
                        $checkTime = $existingEnd; // Salta alla fine della fase che crea conflitto
                        break;
                    }
                }
            }

            if (!$isOverlapping) {
                // Se non c'è sovrapposizione, questo slot è valido
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