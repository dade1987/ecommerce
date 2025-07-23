<?php

namespace Database\Seeders;

use App\Models\Bom;
use App\Models\InternalProduct;
use App\Models\InventoryMovement;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionPhase;
use App\Models\ProductTwin;
use App\Models\Warehouse;
use App\Models\Workstation;
use App\Models\WorkstationAvailability;
use App\Models\OperatorFeedback;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FullProductionDemoSeederNew extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🗑️  Cleaning up old demo data...');
        $this->truncateTables();

        $this->command->info('🏭 Seeding initial static data...');
        $this->seedStaticData();

        // Dati di produzione dell'ultimo mese
        $this->command->info('📅 Seeding last month production data...');
        $this->seedLastMonthData();
        
        $this->command->info('✅ Full production demo seeded successfully!');
    }

    private function seedStaticData()
    {
        // Data from 2025-07-23
        $date = Carbon::parse('2025-07-23');

        DB::table('logistic_warehouses')->insert([
            ['id' => 1, 'name' => 'Magazzino Materie Prime', 'type' => 'magazzino', 'is_final_destination' => 0, 'created_at' => $date->copy()->addHours(6)->addMinutes(14)->addSeconds(3), 'updated_at' => $date->copy()->addHours(6)->addMinutes(14)->addSeconds(3)],
            ['id' => 2, 'name' => 'Magazzino di Distribuzione', 'type' => 'magazzino', 'is_final_destination' => 0, 'created_at' => $date->copy()->addHours(6)->addMinutes(15)->addSeconds(24), 'updated_at' => $date->copy()->addHours(6)->addMinutes(15)->addSeconds(24)],
            ['id' => 3, 'name' => 'Negozio Cavallini', 'type' => 'negozio', 'is_final_destination' => 1, 'created_at' => $date->copy()->addHours(8)->addMinutes(2)->addSeconds(51), 'updated_at' => $date->copy()->addHours(8)->addMinutes(2)->addSeconds(51)],
        ]);

        DB::table('production_lines')->insert([
            ['id' => 1, 'name' => 'Taglio Laser', 'description' => 'Taglio Laser della Lamiera', 'status' => 'active', 'created_at' => $date->copy()->addHours(5)->addMinutes(39)->addSeconds(10), 'updated_at' => $date->copy()->addHours(5)->addMinutes(39)->addSeconds(10)],
            ['id' => 2, 'name' => 'Pressa Idraulica', 'description' => null, 'status' => 'active', 'created_at' => $date->copy()->addHours(10)->addMinutes(25)->addSeconds(24), 'updated_at' => $date->copy()->addHours(10)->addMinutes(25)->addSeconds(24)],
        ]);

        DB::table('workstations')->insert([
            ['id' => 1, 'name' => 'Laser', 'production_line_id' => 1, 'status' => 'active', 'real_time_status' => 'running', 'wear_level' => 0.15, 'last_maintenance_date' => $date->copy()->subDay()->addHours(7), 'error_rate' => 0.5, 'current_speed' => 15, 'capacity' => 8.00, 'batch_size' => 1, 'time_per_unit' => 10, 'created_at' => $date->copy()->addHours(5)->addMinutes(39)->addSeconds(19), 'updated_at' => $date->copy()->addHours(10)->addMinutes(24)->addSeconds(43)],
            ['id' => 2, 'name' => 'Pressa', 'production_line_id' => 2, 'status' => 'active', 'real_time_status' => 'running', 'wear_level' => 1, 'last_maintenance_date' => $date->copy()->subDays(2)->addHours(8), 'error_rate' => 4, 'current_speed' => 2, 'capacity' => 8.00, 'batch_size' => 1, 'time_per_unit' => 10, 'created_at' => $date->copy()->addHours(10)->addMinutes(25)->addSeconds(56), 'updated_at' => $date->copy()->addHours(10)->addMinutes(25)->addSeconds(56)],
        ]);

        DB::table('workstation_availabilities')->insert([
            ['id' => 1, 'workstation_id' => 1, 'day_of_week' => 'monday', 'start_time' => '08:00:00', 'end_time' => '18:00:00', 'is_available' => 1, 'type' => 'regular', 'created_at' => $date->copy()->addHours(5)->addMinutes(46)->addSeconds(50), 'updated_at' => $date->copy()->addHours(5)->addMinutes(51)->addSeconds(28)],
            ['id' => 2, 'workstation_id' => 2, 'day_of_week' => 'monday', 'start_time' => '08:00:00', 'end_time' => '18:00:00', 'is_available' => 1, 'type' => 'regular', 'created_at' => $date->copy()->addHours(10)->addMinutes(31)->addSeconds(43), 'updated_at' => $date->copy()->addHours(10)->addMinutes(31)->addSeconds(43)],
            ['id' => 3, 'workstation_id' => 2, 'day_of_week' => 'tuesday', 'start_time' => '08:00:00', 'end_time' => '18:00:00', 'is_available' => 1, 'type' => 'regular', 'created_at' => $date->copy()->addHours(10)->addMinutes(31)->addSeconds(57), 'updated_at' => $date->copy()->addHours(10)->addMinutes(31)->addSeconds(57)],
        ]);

        DB::table('internal_products')->insert([
            ['id' => 1, 'name' => 'Lamiera 500x200x5', 'code' => '978020137962', 'unit_of_measure' => 'pz', 'weight' => 1.35, 'emission_factor' => 6.8000, 'is_recyclable' => 1, 'created_at' => $date->copy()->addHours(6)->addMinutes(11)->addSeconds(5), 'updated_at' => $date->copy()->addHours(6)->addMinutes(11)->addSeconds(5)],
        ]);

        DB::table('boms')->insert([
            ['id' => 1, 'internal_code' => 'Lamiera Forata 200x500', 'materials' => '[{"material_type":"Alluminio","thickness":"5","quantity":"1"}]', 'internal_product_id' => 1, 'created_at' => $date->copy()->addHours(5)->addMinutes(38)->addSeconds(44), 'updated_at' => $date->copy()->addHours(6)->addMinutes(11)->addSeconds(20)],
        ]);

        DB::table('production_orders')->insert([
            ['id' => 1, 'customer' => 'Gino', 'order_date' => $date->copy()->subDay()->addHours(22), 'status' => 'in_attesa', 'priority' => 0, 'bom_id' => 1, 'quantity' => 1, 'notes' => 'test', 'created_at' => $date->copy()->addHours(5)->addMinutes(45)->addSeconds(4), 'updated_at' => $date->copy()->addHours(5)->addMinutes(47)->addSeconds(44)],
        ]);

        DB::table('production_phases')->insert([
            ['id' => 1, 'production_order_id' => 1, 'workstation_id' => 1, 'name' => 'Taglio', 'estimated_duration' => 60, 'setup_time' => 15, 'scheduled_start_time' => $date->copy()->addDays(5)->addHours(6), 'scheduled_end_time' => $date->copy()->addDays(5)->addHours(7)->addMinutes(15), 'is_completed' => 1, 'is_maintenance' => 0, 'created_at' => $date->copy()->addHours(5)->addMinutes(45)->addSeconds(28), 'updated_at' => $date->copy()->addHours(10)->addMinutes(28)->addSeconds(57)],
            ['id' => 2, 'production_order_id' => 1, 'workstation_id' => 2, 'name' => 'Pressatura', 'estimated_duration' => 60, 'setup_time' => 15, 'scheduled_start_time' => $date->copy()->addDays(5)->addHours(8), 'scheduled_end_time' => $date->copy()->addDays(5)->addHours(10), 'is_completed' => 1, 'is_maintenance' => 0, 'created_at' => $date->copy()->addHours(10)->addMinutes(29)->addSeconds(35), 'updated_at' => $date->copy()->addHours(10)->addMinutes(30)->addSeconds(9)],
        ]);
        
        DB::table('logistic_inventory_movements')->insert([
            [
                'id' => 3,
                'from_warehouse_id' => null,
                'to_warehouse_id' => 1,
                'movement_type' => 'carico',
                'quantity' => 3,
                'note' => null,
                'distance_km' => null,
                'transport_mode' => null,
                'production_order_id' => null,
                'origine_automatica' => 0,
                'created_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59),
                'updated_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59),
                'internal_product_id' => 1
            ],
            [
                'id' => 6,
                'from_warehouse_id' => 1,
                'to_warehouse_id' => 2,
                'movement_type' => 'trasferimento',
                'quantity' => 0,
                'note' => null,
                'distance_km' => 150.00,
                'transport_mode' => 'camion',
                'production_order_id' => null,
                'origine_automatica' => 0,
                'created_at' => $date->copy()->addHours(8)->addMinutes(1)->addSeconds(14),
                'updated_at' => $date->copy()->addHours(8)->addMinutes(1)->addSeconds(14),
                'internal_product_id' => 1
            ],
            [
                'id' => 7,
                'from_warehouse_id' => 2,
                'to_warehouse_id' => 3,
                'movement_type' => 'trasferimento',
                'quantity' => 0,
                'note' => null,
                'distance_km' => 300.00,
                'transport_mode' => 'camion',
                'production_order_id' => null,
                'origine_automatica' => 0,
                'created_at' => $date->copy()->addHours(8)->addMinutes(3)->addSeconds(18),
                'updated_at' => $date->copy()->addHours(8)->addMinutes(3)->addSeconds(18),
                'internal_product_id' => 1
            ],
            [
                'id' => 8,
                'from_warehouse_id' => 3,
                'to_warehouse_id' => null,
                'movement_type' => 'scarico',
                'quantity' => 0,
                'note' => null,
                'distance_km' => null,
                'transport_mode' => null,
                'production_order_id' => null,
                'origine_automatica' => 0,
                'created_at' => $date->copy()->addHours(10)->addMinutes(15)->addSeconds(46),
                'updated_at' => $date->copy()->addHours(10)->addMinutes(15)->addSeconds(46),
                'internal_product_id' => 1
            ],
        ]);
        
        DB::table('product_twins')->insert([
            ['id' => 6, 'uuid' => '2e42456e-5ebb-4376-963c-b83370e6c070', 'internal_product_id' => 1, 'lifecycle_status' => 'sold', 'co2_emissions_production' => 6.80, 'co2_emissions_logistics' => 0.00, 'co2_emissions_total' => 6.80, 'metadata' => '{"carico_inventory_movement_id":3,"note":null}', 'created_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59), 'updated_at' => $date->copy()->addHours(10)->addMinutes(15)->addSeconds(46), 'current_warehouse_id' => null],
            ['id' => 7, 'uuid' => '918df696-4ecc-4374-9a76-e60a981548be', 'internal_product_id' => 1, 'lifecycle_status' => 'in_stock', 'co2_emissions_production' => 6.80, 'co2_emissions_logistics' => 0.00, 'co2_emissions_total' => 6.80, 'metadata' => '{"carico_inventory_movement_id":3,"note":null}', 'created_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59), 'updated_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59), 'current_warehouse_id' => 1],
            ['id' => 8, 'uuid' => '199af246-3057-4258-ba99-ce6a45818e8d', 'internal_product_id' => 1, 'lifecycle_status' => 'in_stock', 'co2_emissions_production' => 6.80, 'co2_emissions_logistics' => 0.00, 'co2_emissions_total' => 6.80, 'metadata' => '{"carico_inventory_movement_id":3,"note":null}', 'created_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59), 'updated_at' => $date->copy()->addHours(7)->addMinutes(36)->addSeconds(59), 'current_warehouse_id' => 1],
        ]);

        DB::table('inventory_movement_product_twin')->insert([
            ['inventory_movement_id' => 3, 'product_twin_id' => 6],
            ['inventory_movement_id' => 3, 'product_twin_id' => 7],
            ['inventory_movement_id' => 3, 'product_twin_id' => 8],
            ['inventory_movement_id' => 6, 'product_twin_id' => 6],
            ['inventory_movement_id' => 7, 'product_twin_id' => 6],
            ['inventory_movement_id' => 8, 'product_twin_id' => 6],
        ]);
    }

    private function seedLastMonthData()
    {
        $start_date = Carbon::now()->subMonth();
        $end_date = Carbon::now();

        for ($date = $start_date; $date->lte($end_date); $date->addDay()) {
            $order_qty = rand(1, 5);
            $production_order = ProductionOrder::create([
                'customer' => 'Cliente ' . $date->format('Y-m-d'),
                'order_date' => $date,
                'status' => 'completato',
                'priority' => rand(0, 1),
                'bom_id' => 1,
                'quantity' => $order_qty,
                'notes' => 'Ordine generato automaticamente',
                'internal_product_id' => 1,
            ]);

            ProductionPhase::create([
                'production_order_id' => $production_order->id,
                'workstation_id' => 1,
                'name' => 'Taglio',
                'estimated_duration' => 60 * $order_qty,
                'setup_time' => 15,
                'scheduled_start_time' => $date->copy()->addHours(8),
                'scheduled_end_time' => $date->copy()->addHours(8)->addMinutes(15 + (60 * $order_qty)),
                'is_completed' => 1,
            ]);

            ProductionPhase::create([
                'production_order_id' => $production_order->id,
                'workstation_id' => 2,
                'name' => 'Pressatura',
                'estimated_duration' => 60 * $order_qty,
                'setup_time' => 15,
                'scheduled_start_time' => $date->copy()->addHours(10),
                'scheduled_end_time' => $date->copy()->addHours(10)->addMinutes(15 + (60 * $order_qty)),
                'is_completed' => 1,
            ]);
        }
    }

    private function truncateTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // Prima la tabella pivot, poi le tabelle con FK
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