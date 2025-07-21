<?php

namespace App\Services\Production;

use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionPhase;
use App\Models\Workstation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProductionSchedulingService
{
    /**
     * Predict potential bottlenecks in the production process.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    public function predictBottlenecks(Carbon $startDate, Carbon $endDate): Collection
    {
        $workstations = Workstation::with('productionLine')->get();
        $days = $startDate->diffInDays($endDate);

        return $workstations->map(function (Workstation $workstation) use ($days) {
            $totalDurationMinutes = ProductionPhase::where('workstation_id', $workstation->id)
                ->where('is_completed', false)
                ->sum('estimated_duration');

            $totalCapacityHours = $workstation->capacity * $days;
            $workloadHours = $totalDurationMinutes / 60;

            $utilization = ($totalCapacityHours > 0) ? ($workloadHours / $totalCapacityHours) * 100 : 0;

            return [
                'workstation_name' => $workstation->name,
                'production_line' => $workstation->productionLine->name,
                'workload_hours' => round($workloadHours, 2),
                'capacity_hours' => round($totalCapacityHours, 2),
                'utilization' => round($utilization, 2),
            ];
        });
    }

    /**
     * Balance the workload across different production lines.
     *
     * @return void
     */
    public function balanceProductionLines(): void
    {
        $unassignedOrders = ProductionOrder::where('status', 'in_attesa')
            ->whereNull('production_line_id')
            ->with('phases')
            ->get();

        $linesLoad = ProductionLine::all()->mapWithKeys(function (ProductionLine $line) {
            return [$line->id => $this->getLineWorkload($line)];
        });

        foreach ($unassignedOrders as $order) {
            $orderWeight = $order->phases->sum('estimated_duration');

            if ($linesLoad->isEmpty()) {
                continue;
            }
            
            $bestLineId = $linesLoad->search($linesLoad->min());
            
            $order->production_line_id = $bestLineId;
            $order->save();

            $linesLoad[$bestLineId] += $orderWeight;
        }
    }

    /**
     * Schedule production phases based on priority and workstation availability.
     * For now, this is a simplified heuristic.
     *
     * @return void
     */
    public function scheduleProduction(): void
    {
        $orders = ProductionOrder::where('status', 'in_attesa')
            ->orderBy('priority', 'desc')
            ->with('phases.workstation')
            ->get();

        foreach ($orders as $order) {
            if ($order->productionLine) {
                $workload = $this->getLineWorkload($order->productionLine);
                $capacity = 1000; // Arbitrary capacity

                if ($workload < $capacity) {
                    $order->status = 'in_produzione';
                    $order->save();

                    // Schedule phases sequentially starting from now
                    $lastPhaseEndTime = now();
                    foreach ($order->phases->where('is_completed', false)->sortBy('id') as $phase) {
                        $phase->scheduled_start_time = $lastPhaseEndTime;
                        $duration = $phase->estimated_duration ?? 60; // Default to 60 mins if not set
                        $phase->scheduled_end_time = $lastPhaseEndTime->copy()->addMinutes($duration);
                        $phase->save();
                        $lastPhaseEndTime = $phase->scheduled_end_time;
                    }
                }
            }
        }
    }

    /**
     * Calculate the workload for a given workstation.
     *
     * @param Workstation $workstation
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function getWorkstationLoad(Workstation $workstation, Carbon $startDate, Carbon $endDate): float
    {
        $totalDurationMinutes = ProductionPhase::where('workstation_id', $workstation->id)
            ->where('is_completed', false)
            ->whereBetween('created_at', [$startDate, $endDate]) // Assumiamo che le fasi siano create nel periodo di interesse
            ->sum('estimated_duration');

        return $totalDurationMinutes / 60; // Return load in hours
    }

    /**
     * Calculate the current workload for a given production line.
     *
     * @param ProductionLine $line
     * @return float
     */
    public function getLineWorkload(ProductionLine $line): float
    {
        return ProductionOrder::where('production_line_id', $line->id)
            ->whereIn('status', ['in_attesa', 'in_produzione'])
            ->with('phases')
            ->get()
            ->sum(function (ProductionOrder $order) {
                return $order->phases->sum('estimated_duration');
            });
    }
} 