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
}
