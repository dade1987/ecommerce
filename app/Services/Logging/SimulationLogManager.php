<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;

class SimulationLogManager
{
    protected string $simulationId;

    public function __construct()
    {
        $this->simulationId = uniqid('sim_');
    }

    /**
     * Avvia una nuova sessione di logging per una simulazione.
     *
     * @param string $simulationType (es. "what-if", "demand_forecast")
     * @param array $initialParams
     */
    public function startLog(string $simulationType, array $initialParams): void
    {
        Log::channel('simulation')->info("[$this->simulationId] Avvio simulazione.", [
            'type' => $simulationType,
            'params' => $initialParams,
        ]);
    }

    /**
     * Registra un passaggio intermedio della simulazione.
     *
     * @param string $message
     * @param array $context
     */
    public function logStep(string $message, array $context = []): void
    {
        Log::channel('simulation')->info("[$this->simulationId] $message", $context);
    }

    /**
     * Registra un errore o un avviso durante la simulazione.
     *
     * @param string $message
     * @param array $context
     */
    public function logWarning(string $message, array $context = []): void
    {
        Log::channel('simulation')->warning("[$this->simulationId] $message", $context);
    }

    /**
     * Finalizza il log della simulazione con i risultati.
     *
     * @param array $results
     */
    public function endLog(array $results): void
    {
        Log::channel('simulation')->info("[$this->simulationId] Simulazione completata.", [
            'results' => $results,
        ]);
    }
} 