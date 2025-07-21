<?php

namespace App\Services\Forecasting;

use App\Models\ProductionOrder;
use App\Services\Logging\SimulationLogManager;
use Carbon\Carbon;

class DemandForecastingService
{
    protected SimulationLogManager $logManager;

    public function __construct()
    {
        $this->logManager = new SimulationLogManager();
    }

    /**
     * Prevede la domanda futura usando una media mobile ponderata.
     *
     * @param int $months Periodo su cui basare la media.
     * @param array $weights Pesi da assegnare a ciascun mese (dal più recente al più vecchio).
     * @return array
     */
    public function predictDemand(int $months = 3, array $weights = [0.5, 0.3, 0.2]): array
    {
        $params = ['months' => $months, 'weights' => $weights];
        $this->logManager->startLog('demand_forecast_wma', $params);

        if (array_sum($weights) !== 1.0 || count($weights) !== $months) {
            $this->logManager->logWarning('La somma dei pesi non è 1 o il numero di pesi non corrisponde ai mesi.', $params);
            throw new \InvalidArgumentException('La somma dei pesi deve essere 1 e il numero di pesi deve corrispondere ai mesi.');
        }

        $historicalData = [];
        for ($i = 0; $i < $months; $i++) {
            $date = Carbon::now()->subMonths($i + 1);
            $ordersCount = ProductionOrder::whereYear('order_date', $date->year)
                                           ->whereMonth('order_date', $date->month)
                                           ->count();
            $historicalData[] = $ordersCount;
            $this->logManager->logStep("Dati storici per {$date->format('F Y')}: {$ordersCount} ordini.");
        }

        $forecastVolume = 0;
        foreach ($historicalData as $index => $count) {
            $forecastVolume += $count * $weights[$index];
        }
        
        $forecast = [
            'next_month_volume' => round($forecastVolume),
            'based_on_months' => $months,
            'calculation_method' => 'Weighted Moving Average',
        ];
        
        $this->logManager->endLog($forecast);

        return [
            'forecast' => $forecast,
        ];
    }
} 