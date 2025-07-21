<?php

namespace App\Console\Commands;

use App\Models\Bom;
use App\Models\ProductionOrder;
use App\Services\Production\AdvancedSchedulingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestSimulationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:simulation {scenario}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esegue uno scenario di test di simulazione per il sistema di produzione.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $scenario = $this->argument('scenario');
        $this->info("Avvio scenario di test: {$scenario}");
        Log::channel('simulation')->info("--- INIZIO SCENARIO DI TEST: {$scenario} ---");

        switch ($scenario) {
            case 'urgent-order':
                $this->runUrgentOrderTest();
                break;
            case 'machine-failure':
                $this->runMachineFailureTest();
                break;
            case 'efficiency-variation':
                $this->runEfficiencyVariationTest();
                break;
            case 'change-priority':
                $this->runChangePriorityTest();
                break;
            // Aggiungere qui altri casi di test
            default:
                $this->error("Scenario '{$scenario}' non riconosciuto.");
                Log::channel('simulation')->error("Scenario non riconosciuto: {$scenario}");
                return 1;
        }

        Log::channel('simulation')->info("--- FINE SCENARIO DI TEST: {$scenario} ---");
        $this->info("Scenario di test completato. Controlla il file simulation.log per i dettagli.");

        return 0;
    }

    private function runUrgentOrderTest()
    {
        $this->comment('Esecuzione test: Inserimento Ordine Urgente...');

        // 1. Creazione di un ordine urgente fittizio
        $urgentOrder = ProductionOrder::create([
            'customer' => 'Cliente VIP Urgente',
            'order_date' => now(),
            'status' => 'in_attesa',
            'priority' => 5, // Massima priorità
            'bom_id' => Bom::inRandomOrder()->first()->id,
            'notes' => 'Ordine generato dallo scenario di test `urgent-order`.',
        ]);
        $this->info("Creato ordine urgente ID: {$urgentOrder->id}");
        Log::channel('simulation')->info("[urgent-order] Creato ordine urgente ID: {$urgentOrder->id}");

        // Qui si potrebbero aggiungere le fasi all'ordine...

        // 2. Esecuzione della schedulazione avanzata per vedere l'impatto
        $this->comment('Esecuzione della schedulazione avanzata per misurare l\'impatto...');
        $scheduler = new AdvancedSchedulingService();
        $result = $scheduler->generateSchedule();
        
        $logSummary = implode("\n", $result['log']);
        Log::channel('simulation')->info("[urgent-order] Risultati della schedulazione:\n{$logSummary}");
        
        // 3. Analisi dei KPI (placeholder)
        $this->comment('Analisi dei KPI post-simulazione (placeholder)...');
        $kpi = [
            'delayed_orders' => 'N/A', // Da calcolare
            'avg_lead_time_increase' => 'N/A', // Da calcolare
        ];
        Log::channel('simulation')->info("[urgent-order] KPI Rilevati:", $kpi);
        $this->table(['KPI', 'Valore'], array_map(fn($k, $v) => [$k, $v], array_keys($kpi), $kpi));
    }

    private function runMachineFailureTest()
    {
        $this->comment('Esecuzione test: Guasto Macchina Simulato...');

        $workstation = \App\Models\Workstation::where('status', 'active')->inRandomOrder()->first();
        if (!$workstation) {
            $this->error('Nessuna postazione di lavoro attiva trovata per il test.');
            return;
        }

        $originalStatus = $workstation->real_time_status;
        $this->info("Simulazione guasto sulla postazione: {$workstation->name} (ID: {$workstation->id})");
        Log::channel('simulation')->info("[machine-failure] Postazione selezionata per il guasto: {$workstation->name} (ID: {$workstation->id})");

        // 1. Simula il guasto
        $workstation->update(['real_time_status' => 'faulted']);
        
        // 2. Aggiungi un blocco di manutenzione non programmata per simulare l'indisponibilità
        // Nota: questo richiede che l'algoritmo di schedulazione consideri le fasi di manutenzione
        // come blocchi prioritari e non spostabili.
        $maintenance_duration = 120; // 2 ore di guasto
        $maintenance_phase = \App\Models\ProductionPhase::create([
            'production_order_id' => ProductionOrder::first()->id, // Associato a un ordine a caso
            'workstation_id' => $workstation->id,
            'name' => 'Manutenzione Non Programmata (Guasto)',
            'is_maintenance' => true,
            'scheduled_start_time' => now(),
            'scheduled_end_time' => now()->addMinutes($maintenance_duration),
        ]);
        Log::channel('simulation')->info("[machine-failure] Creato blocco di manutenzione non programmata ID: {$maintenance_phase->id} per {$maintenance_duration} minuti.");

        // 3. Esegui la schedulazione per vedere come il sistema ripianifica
        $this->comment('Esecuzione della schedulazione avanzata per la ripianificazione...');
        $scheduler = new AdvancedSchedulingService();
        $result = $scheduler->generateSchedule();
        $logSummary = implode("\n", $result['log']);
        Log::channel('simulation')->info("[machine-failure] Risultati della ripianificazione:\n{$logSummary}");

        // 4. Ripristina lo stato originale
        $workstation->update(['real_time_status' => $originalStatus]);
        $maintenance_phase->delete(); // Rimuovi il blocco di manutenzione del test
        $this->info("Stato della postazione {$workstation->name} ripristinato.");
        Log::channel('simulation')->info("[machine-failure] Stato della postazione ripristinato.");

        // 5. Analisi KPI (placeholder)
        $this->comment('Analisi dei KPI post-guasto (placeholder)...');
        $kpi = [
            'impacted_orders' => 'N/A',
            'oee_reduction' => 'N/A',
        ];
        Log::channel('simulation')->info("[machine-failure] KPI Rilevati:", $kpi);
        $this->table(['KPI', 'Valore'], array_map(fn($k, $v) => [$k, $v], array_keys($kpi), $kpi));
    }

    private function runEfficiencyVariationTest()
    {
        $this->comment('Esecuzione test: Variazione Efficienza Operatore/Macchina...');

        $workstation = \App\Models\Workstation::where('status', 'active')->inRandomOrder()->first();
        if (!$workstation) {
            $this->error('Nessuna postazione di lavoro attiva trovata per il test.');
            return;
        }

        $this->info("Simulazione calo di efficienza sulla postazione: {$workstation->name} (ID: {$workstation->id})");
        Log::channel('simulation')->info("[efficiency-variation] Postazione selezionata: {$workstation->name} (ID: {$workstation->id})");

        $oeeService = new \App\Services\Production\OeeService();

        // 1. Calcola OEE iniziale
        $oeeBefore = $oeeService->calculateForWorkstation($workstation);
        Log::channel('simulation')->info("[efficiency-variation] OEE Iniziale:", $oeeBefore);


        // 2. Simula il calo di efficienza (-20%)
        // Aumentiamo il tempo per unità per riflettere un calo di performance nella pianificazione
        $originalTimePerUnit = $workstation->time_per_unit;
        $newTimePerUnit = $originalTimePerUnit * 1.20; 
        $workstation->update(['time_per_unit' => $newTimePerUnit]);
        $this->info("Tempo per unità aumentato da {$originalTimePerUnit} a {$newTimePerUnit} min.");
        Log::channel('simulation')->info("[efficiency-variation] Efficienza ridotta. Tempo per unità passato da {$originalTimePerUnit} a {$newTimePerUnit} min.");


        // 3. Calcola OEE dopo il calo
        $oeeAfter = $oeeService->calculateForWorkstation($workstation);
        Log::channel('simulation')->info("[efficiency-variation] OEE Dopo il calo:", $oeeAfter);
        
        // 4. Esegui schedulazione per vedere l'impatto sul lead time
        $this->comment('Esecuzione della schedulazione avanzata per misurare l\'impatto sui tempi...');
        $scheduler = new AdvancedSchedulingService();
        $result = $scheduler->generateSchedule();
        Log::channel('simulation')->info("[efficiency-variation] La nuova schedulazione mostrerà durate maggiori per le fasi impattate.");


        // 5. Ripristina i valori originali
        $workstation->update(['time_per_unit' => $originalTimePerUnit]);
        $this->info("Efficienza della postazione {$workstation->name} ripristinata.");
        Log::channel('simulation')->info("[efficiency-variation] Efficienza ripristinata.");

        // 6. Mostra i KPI
        $this->comment('Analisi dei KPI di efficienza...');
        $kpi = [
            'OEE Iniziale' => round($oeeBefore['oee'] * 100, 2) . '%',
            'OEE Finale' => round($oeeAfter['oee'] * 100, 2) . '%',
            'Riduzione OEE' => round(($oeeBefore['oee'] - $oeeAfter['oee']) * 100, 2) . '%',
            'Impatto su Lead Time' => 'Visibile nel Gantt generato (durate fasi aumentate)',
        ];
        $this->table(['KPI', 'Valore'], array_map(fn($k, $v) => [$k, $v], array_keys($kpi), $kpi));
    }

    private function runChangePriorityTest()
    {
        $this->comment('Esecuzione test: Cambio di Priorità a un Ordine Esistente...');

        $order = ProductionOrder::where('status', 'in_attesa')->where('priority', '<', 4)->inRandomOrder()->first();
        if (!$order) {
            $this->error('Nessun ordine a bassa priorità trovato per il test.');
            return;
        }

        $this->info("Ordine selezionato per il cambio priorità: ID {$order->id}, Priorità iniziale: {$order->priority}");
        Log::channel('simulation')->info("[change-priority] Ordine selezionato: ID {$order->id}, Priorità iniziale: {$order->priority}");

        // 1. Aumenta la priorità
        $originalPriority = $order->priority;
        $order->update(['priority' => 5]);
        $this->info("Priorità dell'ordine ID {$order->id} aumentata a 5.");

        // 2. Esegui la schedulazione per vedere l'impatto
        $this->comment('Esecuzione della schedulazione avanzata...');
        $scheduler = new AdvancedSchedulingService();
        $result = $scheduler->generateSchedule();
        $logSummary = implode("\n", $result['log']);
        Log::channel('simulation')->info("[change-priority] Risultati della schedulazione:\n{$logSummary}");
        $this->comment('Nei log di simulazione, l\'ordine con priorità modificata dovrebbe apparire tra i primi gruppi processati.');
        
        // 3. Ripristina la priorità originale
        $order->update(['priority' => $originalPriority]);
        $this->info("Priorità dell'ordine ID {$order->id} ripristinata a {$originalPriority}.");
        Log::channel('simulation')->info("[change-priority] Priorità ripristinata.");

        // 4. Analisi KPI (placeholder)
        $this->comment('Analisi dei KPI (placeholder)...');
        $kpi = [
            'Reprioritization_Impact' => 'Verificabile dall\'ordine di schedulazione nel log.',
        ];
        $this->table(['KPI', 'Valore'], array_map(fn($k, $v) => [$k, $v], array_keys($kpi), $kpi));
    }
}
