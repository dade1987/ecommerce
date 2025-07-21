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
                
                $slot = $this->findNextAvailableSlot($workstation, $lastPhaseEndTime, $estimatedDuration, $scheduledPhasesData);
                
                $phase->scheduled_start_time = $slot['start'];
                $phase->scheduled_end_time = $slot['end'];
                $phase->save();

                $scheduledPhasesData[] = [
                    'workstation_id' => $workstation->id,
                    'scheduled_start_time' => $slot['start']->toDateTimeString(),
                    'scheduled_end_time' => $slot['end']->toDateTimeString(),
                ];
                $lastPhaseEndTime = $slot['end'];

                $log[] = "Scheduled phase {$phase->name} (ID: {$phase->id}) on {$workstation->name} from {$slot['start']} to {$slot['end']}.";
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
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    private function findNextAvailableSlot(Workstation $workstation, Carbon $earliestStartTime, int $durationMinutes, array &$allScheduledPhases): array
    {
        $searchTime = $earliestStartTime->copy();

        while (true) {
            $potentialStart = $this->findNextWorkingTime($workstation, $searchTime);
            $potentialEnd = $potentialStart->copy()->addMinutes($durationMinutes);

            if (!$this->isSlotValid($workstation, $potentialStart, $potentialEnd)) {
                $searchTime = $potentialStart->addMinute();
                continue;
            }

            $isOverlapping = false;
            foreach ($allScheduledPhases as $scheduledPhase) {
                if ($scheduledPhase['workstation_id'] !== $workstation->id) continue;

                $existingStart = Carbon::parse($scheduledPhase['scheduled_start_time']);
                $existingEnd = Carbon::parse($scheduledPhase['scheduled_end_time']);

                if ($potentialEnd > $existingStart && $potentialStart < $existingEnd) {
                    $searchTime = $existingEnd->copy();
                    $isOverlapping = true;
                    break;
                }
            }

            if ($isOverlapping) {
                continue;
            }
            
            return ['start' => $potentialStart, 'end' => $potentialEnd];
        }
    }

    private function findNextWorkingTime(Workstation $workstation, Carbon $time): Carbon
    {
        $time = $time->copy();

        // If we are outside working hours, move to the beginning of the next working day.
        // This logic could be expanded to read from the availabilities table.
        if ($time->hour >= 18) {
            $time->addDay()->startOfDay()->hour = 8;
        }

        if ($time->hour < 8) {
            $time->startOfDay()->hour = 8;
        }

        // If the calculated time is a weekend, move to the next Monday at 8:00.
        if ($time->isWeekend()) {
            $time->next(Carbon::MONDAY)->startOfDay()->hour = 8;
        }

        return $time;
    }

    private function isSlotValid(Workstation $workstation, Carbon $start, Carbon $end): bool
    {
        // For simplicity, we assume tasks cannot run overnight.
        if (!$start->isSameDay($end)) {
            return false;
        }
        
        // Check if the day is a weekend.
        if ($start->isWeekend()) {
            return false;
        }

        // Define working hours for the day.
        $workingStartTime = $start->copy()->startOfDay()->hour(8);
        $workingEndTime = $start->copy()->startOfDay()->hour(18);

        // Check if the entire slot is within the working hours.
        if ($start < $workingStartTime || $end > $workingEndTime) {
            return false;
        }

        return true;
    }
} 