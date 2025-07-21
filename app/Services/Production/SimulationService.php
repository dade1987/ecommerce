<?php

namespace App\Services\Production;

use App\Models\ProductionOrder;
use App\Services\Logging\SimulationLogManager;
use Illuminate\Support\Collection;

class SimulationService
{
    protected SimulationLogManager $logManager;

    public function __construct()
    {
        $this->logManager = new SimulationLogManager();
    }
    /**
     * Esegue una simulazione "what-if" per un nuovo ordine.
     *
     * @param array $newOrderData I dati del nuovo ordine da simulare.
     * @return array Il risultato della simulazione.
     */
    public function runWhatIfSimulation(array $newOrderData): array
    {
        $this->logManager->startLog('what-if', $newOrderData);
        
        // 1. Recupera gli ordini reali in attesa
        $realOrders = ProductionOrder::whereIn('status', ['in_attesa'])
            ->with('bom', 'phases.workstation')
            ->get();
        $this->logManager->logStep("Recuperati " . $realOrders->count() . " ordini reali in attesa.");

        // 2. Crea un nuovo ordine fittizio in memoria
        $hypotheticalOrder = new ProductionOrder($newOrderData);
        $hypotheticalOrder->id = 99999; // ID fittizio per il logging
        // Aggiungi le fasi basate sul BOM
        $bom = \App\Models\Bom::with('phases.workstation')->find($newOrderData['bom_id']);
        if ($bom) {
            foreach ($bom->phases as $phaseTemplate) {
                $hypotheticalOrder->phases->add(new \App\Models\ProductionPhase($phaseTemplate->toArray()));
            }
        }
        $this->logManager->logStep("Creato ordine ipotetico '{$hypotheticalOrder->customer}' in memoria.");

        // 3. Unisci gli ordini reali e quello ipotetico
        $ordersForSimulation = $realOrders->push($hypotheticalOrder);

        // 4. Esegui la schedulazione sulla collezione combinata
        $scheduler = new AdvancedSchedulingService();
        $simulationResult = $scheduler->generateSchedule($ordersForSimulation);

        $this->logManager->logStep("Schedulazione simulata completata.");
        $this->logManager->endLog(['simulation_log' => $simulationResult['log']]);

        return [
            'log' => $simulationResult['log'],
            'scheduled_phases' => $simulationResult['scheduled_phases_data'] ?? [], // Assicurati che questo venga restituito dallo scheduler
        ];
    }
} 