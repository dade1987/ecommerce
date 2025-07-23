<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanProductionDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🗑️  Svuotamento tabelle demo produzione, logistica e tracciabilità...');
        $this->truncateTables();
        $this->command->info('✅ Tabelle svuotate. Ora puoi inserire dati manualmente.');
    }

    private function truncateTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $tables = [
            'inventory_movement_product_twin',
            'product_twins',
            'logistic_inventory_movements',
            'production_phases',
            'production_orders',
            'boms',
            'internal_products',
            'workstation_availabilities',
            'workstations',
            'production_lines',
            'logistic_warehouses',
            'operator_feedback',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
} 