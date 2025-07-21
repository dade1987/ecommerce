<?php

namespace App\Services\Forecasting;

use App\Models\ProductionOrder;
use App\Services\Logging\SimulationLogManager;
use Carbon\Carbon;
use InvalidArgumentException;

class DemandForecastingService
{
    protected SimulationLogManager $logManager;

    public function __construct()
    {
        $this->logManager = new SimulationLogManager();
    }

    /**
     * Prevede la domanda futura usando il metodo di Holt (Double Exponential Smoothing).
     *
     * @param int $periodsToForecast Numero di periodi futuri da prevedere.
     * @param float $alpha Fattore di smorzamento per il livello (0 < alpha < 1).
     * @param float $beta Fattore di smorzamento per il trend (0 < beta < 1).
     * @return array
     */
    public function predictDemand(int $periodsToForecast = 1, float $alpha = 0.3, float $beta = 0.2): array
    {
        $params = ['alpha' => $alpha, 'beta' => $beta, 'periods' => $periodsToForecast];
        $this->logManager->startLog('demand_forecast_holt', $params);

        if ($alpha <= 0 || $alpha >= 1 || $beta <= 0 || $beta >= 1) {
            $this->logManager->logWarning('Alpha e Beta devono essere compresi tra 0 e 1.', $params);
            throw new InvalidArgumentException('I fattori di smorzamento Alpha e Beta devono essere compresi tra 0 e 1.');
        }

        // Recupera i dati storici degli ultimi 12 mesi per avere una serie storica robusta
        $series = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $series[] = ProductionOrder::whereYear('order_date', $date->year)
                                       ->whereMonth('order_date', $date->month)
                                       ->count();
        }
        $this->logManager->logStep("Serie storica recuperata (12 mesi): " . implode(', ', $series));

        if (count($series) < 2) {
             $this->logManager->logWarning('La serie storica è troppo corta per calcolare il trend.', ['series_count' => count($series)]);
            throw new InvalidArgumentException('Sono necessari almeno due periodi di dati per calcolare il trend iniziale.');
        }

        // Inizializzazione
        $level = $series[0];
        $trend = $series[1] - $series[0]; // Trend iniziale

        // Calcolo
        for ($i = 1; $i < count($series); $i++) {
            $lastLevel = $level;
            $level = $alpha * $series[$i] + (1 - $alpha) * ($lastLevel + $trend);
            $trend = $beta * ($level - $lastLevel) + (1 - $beta) * $trend;
        }
        $this->logManager->logStep("Calcolo completato. Ultimo livello: {$level}, Ultimo trend: {$trend}");

        // Previsione
        $forecastValue = $level + ($periodsToForecast * $trend);

        $forecast = [
            'next_month_volume' => round(max(0, $forecastValue)), // Assicura che la previsione non sia negativa
            'trend_per_month' => round($trend, 2),
            'calculation_method' => "Holt's Double Exponential Smoothing",
        ];
        
        $this->logManager->endLog($forecast);

        return [
            'forecast' => $forecast,
        ];
    }
} 