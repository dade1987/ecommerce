<?php

namespace App\Console\Commands;

use Database\Seeders\LogisticProductSeeder;
use Database\Seeders\WarehouseSeeder;
use Database\Seeders\InventoryMovementSeeder;
use Database\Seeders\OperatorFeedbackSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedLogisticsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logistics:seed 
                           {--fresh : Pulisce le tabelle prima di inserire i dati}
                           {--only= : Esegue solo un seeder specifico (products|warehouses|movements|feedback)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Popola le tabelle della logistica con dati di esempio realistici';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Inizializzazione seeding logistica...');
        $this->newLine();

        // Opzione fresh: pulisce le tabelle prima di inserire
        if ($this->option('fresh')) {
            $this->warn('⚠️  Modalità FRESH attivata - verranno eliminate tutti i dati esistenti!');
            
            if (!$this->confirm('Sei sicuro di voler procedere?')) {
                $this->info('❌ Operazione annullata.');
                return;
            }
            
            $this->info('🧹 Pulizia tabelle in corso...');
            $this->truncateTables();
            $this->newLine();
        }

        // Opzione only: esegue solo un seeder specifico
        if ($only = $this->option('only')) {
            $this->runSpecificSeeder($only);
            return;
        }

        // Esecuzione completa di tutti i seeder nell'ordine corretto
        $this->runAllSeeders();
    }

    /**
     * Esegue tutti i seeder nell'ordine corretto
     */
    private function runAllSeeders(): void
    {
        $this->info('📦 Esecuzione seeder completa...');
        $this->newLine();

        // 1. Prodotti logistici (base)
        $this->info('1️⃣  Seeding prodotti logistici...');
        $this->callSilentSeeder(LogisticProductSeeder::class);
        
        // 2. Magazzini/Fornitori/Negozi (base)
        $this->info('2️⃣  Seeding magazzini, fornitori e negozi...');
        $this->callSilentSeeder(WarehouseSeeder::class);
        
        // 3. Feedback operatori (indipendente)
        $this->info('3️⃣  Seeding feedback operatori...');
        $this->callSilentSeeder(OperatorFeedbackSeeder::class);
        
        // 4. Movimenti inventario (dipende da prodotti e magazzini)
        $this->info('4️⃣  Seeding movimenti inventario...');
        $this->callSilentSeeder(InventoryMovementSeeder::class);

        $this->newLine();
        $this->info('🎉 Seeding logistica completato con successo!');
        $this->displaySummary();
    }

    /**
     * Esegue un seeder specifico
     */
    private function runSpecificSeeder(string $seederType): void
    {
        $seederClass = match ($seederType) {
            'products' => LogisticProductSeeder::class,
            'warehouses' => WarehouseSeeder::class,
            'movements' => InventoryMovementSeeder::class,
            'feedback' => OperatorFeedbackSeeder::class,
            default => null
        };

        if (!$seederClass) {
            $this->error("❌ Seeder '{$seederType}' non riconosciuto.");
            $this->info('Seeder disponibili: products, warehouses, movements, feedback');
            return;
        }

        $this->info("🎯 Esecuzione seeder: {$seederType}");
        $this->callSilentSeeder($seederClass);
        $this->info('✅ Seeder completato!');
    }

    /**
     * Pulisce le tabelle della logistica
     */
    private function truncateTables(): void
    {
        // Ordine di pulizia: prima le tabelle con foreign key, poi quelle base
        $tables = [
            'logistic_inventory_movements',
            'operator_feedback',
            'logistic_products',
            'logistic_warehouses'
        ];

        foreach ($tables as $table) {
            DB::statement("SET FOREIGN_KEY_CHECKS = 0");
            DB::table($table)->truncate();
            DB::statement("SET FOREIGN_KEY_CHECKS = 1");
            $this->line("  - Tabella {$table} pulita");
        }
    }

    /**
     * Chiama un seeder senza output verboso
     */
    private function callSilentSeeder(string $seederClass): void
    {
        try {
            $seeder = new $seederClass();
            $seeder->setCommand($this);
            $seeder->run();
        } catch (\Exception $e) {
            $this->error("❌ Errore durante l'esecuzione del seeder {$seederClass}:");
            $this->error($e->getMessage());
        }
    }

    /**
     * Mostra un riepilogo dei dati inseriti
     */
    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('📊 Riepilogo dati inseriti:');
        
        $productCount = \App\Models\LogisticProduct::count();
        $warehouseCount = \App\Models\Warehouse::count();
        $movementCount = \App\Models\InventoryMovement::count();
        $feedbackCount = \App\Models\OperatorFeedback::count();

        $this->table(
            ['Tabella', 'Record'],
            [
                ['Prodotti Logistici', $productCount],
                ['Magazzini/Fornitori/Negozi', $warehouseCount],
                ['Movimenti Inventario', $movementCount],
                ['Feedback Operatori', $feedbackCount],
            ]
        );

        $this->newLine();
        $this->info('🔗 Accesso sistema:');
        $this->line('  • Admin Filament: /admin');
        $this->line('  • Giacenze: /admin/inventory-overview');
        $this->line('  • API Feedback: /api/operator-feedback');
        $this->line('  • API Test: /api/operator-feedback/ping');
        
        $this->newLine();
        $this->info('💡 Comandi utili:');
        $this->line('  • php artisan logistics:seed --fresh      # Pulisce e ricarica tutto');
        $this->line('  • php artisan logistics:seed --only=products  # Solo prodotti');
        $this->line('  • php artisan logistics:seed --only=movements # Solo movimenti');
    }
}
