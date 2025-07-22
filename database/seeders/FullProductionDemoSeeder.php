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

class FullProductionDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🗑️  Cleaning up old demo data...');
        $this->truncateTables();

        $this->command->info('🏭 Creating production infrastructure...');
        $infra = $this->createInfrastructure();

        $this->command->info('🔧 Creating products and BOMs...');
        $products = $this->createProducts();
        $this->createBoms($products);

        $this->command->info('📦 Creating production orders and phases...');
        $this->createProduction($products, $infra);
        
        $this->command->info('🚚 Simulating inventory movements...');
        $this->createMovements($infra['warehouses']);

        $this->command->info('📝 Creating operator feedback...');
        $this->createFeedback($infra['workstations']);

        $this->command->info('✅ Full production demo seeded successfully!');
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

    private function createInfrastructure(): array
    {
        $warehouses = [
            'raw' => Warehouse::create(['name' => 'Magazzino Materie Prime', 'type' => 'magazzino']),
            'finished' => Warehouse::create(['name' => 'Magazzino Prodotti Finiti', 'type' => 'magazzino']),
            'milan' => Warehouse::create(['name' => 'Negozio Milano', 'type' => 'negozio', 'is_final_destination' => true]),
        ];

        $lines = [
            'laser' => ProductionLine::create(['name' => 'Linea Taglio Laser', 'status' => 'active']),
            'stamping' => ProductionLine::create(['name' => 'Linea Stampaggio', 'status' => 'active']),
        ];

        $workstations = [
            'laser_cutter' => Workstation::create([
                'production_line_id' => $lines['laser']->id,
                'name' => 'Taglio Laser #1',
                'capacity' => 8,
                'batch_size' => 10,
                'time_per_unit' => 3, // 3 min/unit
                'wear_level' => 15,
                'error_rate' => 2.5, // 2.5% difettosi
            ]),
            'stamping_press' => Workstation::create([
                'production_line_id' => $lines['stamping']->id,
                'name' => 'Pressa #1',
                'capacity' => 8,
                'batch_size' => 100,
                'time_per_unit' => 1.2, // 1.2 min/unit
                'wear_level' => 25,
                'error_rate' => 4.0, // 4% difettosi
            ]),
        ];

        // Availabilities: una fascia 8-17, lun-ven (senza campo date)
        foreach ($workstations as $workstation) {
            for ($day = 1; $day <= 5; $day++) {
                WorkstationAvailability::create([
                    'workstation_id' => $workstation->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
        }
        return compact('warehouses', 'lines', 'workstations');
    }

    private function createProducts(): array
    {
        return [
            'sheet' => InternalProduct::create(['name' => 'Lamiera Forata 100x200', 'code' => 'LAM-F-100x200', 'unit_of_measure' => 'pz', 'weight' => 5.5, 'emission_factor' => 0.15]),
            'bracket' => InternalProduct::create(['name' => 'Staffa Angolare Stampata', 'code' => 'STA-A-001', 'unit_of_measure' => 'pz', 'weight' => 0.7, 'emission_factor' => 0.12]),
            'plate' => InternalProduct::create(['name' => 'Lamiera Piegata 50x150', 'code' => 'LAM-P-50x150', 'unit_of_measure' => 'pz', 'weight' => 3.2, 'emission_factor' => 0.14]),
        ];
    }

    private function createBoms(array $products)
    {
        Bom::create(['internal_product_id' => $products['sheet']->id, 'internal_code' => $products['sheet']->code, 'materials' => json_encode([['material_type' => 'Acciaio Grezzo', 'thickness' => 2, 'quantity' => 1]])]);
        Bom::create(['internal_product_id' => $products['bracket']->id, 'internal_code' => $products['bracket']->code, 'materials' => json_encode([['material_type' => 'Nastro Acciaio', 'thickness' => 1, 'quantity' => 1]])]);
        Bom::create(['internal_product_id' => $products['plate']->id, 'internal_code' => $products['plate']->code, 'materials' => json_encode([['material_type' => 'Acciaio Grezzo', 'thickness' => 3, 'quantity' => 1]])]);
    }

    private function createProduction(array $products, array $infra)
    {
        // Genera fasi di produzione distribuite su più giorni, sia completate che in corso, con durate e quantità variabili
        // (le fasi sono già create in createProduction, ma qui ne aggiungiamo altre per coprire più casi)
        $workstations = $infra['workstations'];
        $now = now();
        $spreadDays = 28; // 4 settimane
        foreach (['laser_cutter', 'stamping_press'] as $wsKey) {
            $ws = $workstations[$wsKey];
            for ($i = 0; $i < 8; $i++) {
                // Distribuisci le date tra passato e futuro
                $offset = rand(-14, 14); // da -14 a +14 giorni rispetto ad oggi
                $orderDate = $now->copy()->addDays($offset);
                $start = $orderDate->copy()->setHour(rand(8, 15))->setMinute(0);
                $duration = rand(30, 180); // 30-180 min
                $isCompleted = $i < 4;
                $order = ProductionOrder::create([
                    'internal_product_id' => $wsKey === 'laser_cutter' ? $products['sheet']->id : $products['bracket']->id,
                    'bom_id' => $wsKey === 'laser_cutter' ? 1 : 2,
                    'production_line_id' => $ws->production_line_id,
                    'quantity' => rand(10, 60),
                    'status' => $i < 5 ? 'in_produzione' : 'in_attesa',
                    'order_date' => $orderDate,
                    'priority' => rand(1, 5),
                    'customer' => 'Cliente Extra ' . $ws->name . ' ' . $i,
                ]);
                ProductionPhase::create([
                    'production_order_id' => $order->id,
                    'workstation_id' => $ws->id,
                    'name' => 'Fase Extra ' . $i,
                    'estimated_duration' => $duration,
                    'energy_consumption' => rand(10, 100),
                    'is_completed' => $isCompleted,
                    'start_time' => $start,
                    'end_time' => $isCompleted ? $start->copy()->addMinutes($duration) : null,
                    'scheduled_start_time' => $start->copy()->addDays(rand(-2, 2)),
                    'scheduled_end_time' => $start->copy()->addDays(rand(-2, 2))->addMinutes($duration),
                ]);
            }
        }
        // Ordini linea LASER
        // 1 completato, 3 in produzione, 1 in attesa
        $sheetOrder = ProductionOrder::create([
            'internal_product_id' => $products['sheet']->id,
            'bom_id' => 1,
            'production_line_id' => $infra['lines']['laser']->id,
            'quantity' => 80,
            'status' => 'completato',
            'order_date' => now()->subDays(15),
            'priority' => 3,
            'customer' => 'Cliente A',
        ]);
        ProductionPhase::create([
            'production_order_id' => $sheetOrder->id,
            'workstation_id' => $infra['workstations']['laser_cutter']->id,
            'name' => 'Taglio e Foratura',
            'estimated_duration' => 250,
            'energy_consumption' => 150,
            'is_completed' => true,
            'start_time' => now()->subDays(14)->setHour(8),
            'end_time' => now()->subDays(14)->setHour(12),
            'scheduled_start_time' => now()->subDays(14)->setHour(8),
            'scheduled_end_time' => now()->subDays(14)->setHour(12),
        ]);
        $this->createTwinsForOrder($sheetOrder, $infra['warehouses']['finished']);

        for ($i = 0; $i < 3; $i++) {
            $order = ProductionOrder::create([
                'internal_product_id' => $products['sheet']->id,
                'bom_id' => 1,
                'production_line_id' => $infra['lines']['laser']->id,
                'quantity' => 40 + $i * 10,
                'status' => 'in_produzione',
                'order_date' => now()->subDays(7 - $i),
                'priority' => 4 - $i,
                'customer' => 'Cliente Laser ' . chr(66 + $i),
            ]);
            ProductionPhase::create([
                'production_order_id' => $order->id,
                'workstation_id' => $infra['workstations']['laser_cutter']->id,
                'name' => 'Taglio e Foratura',
                'estimated_duration' => 120 + $i * 30,
                'energy_consumption' => 80 + $i * 20,
                'is_completed' => false,
                'start_time' => now()->subDays(6 - $i)->setHour(8),
                'end_time' => now()->subDays(6 - $i)->setHour(12),
                'scheduled_start_time' => now()->subDays(6 - $i)->setHour(8),
                'scheduled_end_time' => now()->subDays(6 - $i)->setHour(12),
            ]);
        }
        $laserAttesa = ProductionOrder::create([
            'internal_product_id' => $products['sheet']->id,
            'bom_id' => 1,
            'production_line_id' => $infra['lines']['laser']->id,
            'quantity' => 12,
            'status' => 'in_attesa',
            'order_date' => now()->subDays(1),
            'priority' => 2,
            'customer' => 'Cliente Laser Attesa',
        ]);
        ProductionPhase::create([
            'production_order_id' => $laserAttesa->id,
            'workstation_id' => $infra['workstations']['laser_cutter']->id,
            'name' => 'Taglio Lamiere',
            'estimated_duration' => 60,
            'energy_consumption' => 30,
            'is_completed' => false,
            'start_time' => now()->addDay()->setHour(9),
            'end_time' => now()->addDay()->setHour(10),
            'scheduled_start_time' => null,
            'scheduled_end_time' => null,
        ]);

        // Ordini linea STAMPING
        // 3 in produzione, 1 in attesa
        for ($i = 0; $i < 3; $i++) {
            $order = ProductionOrder::create([
                'internal_product_id' => $products['bracket']->id,
                'bom_id' => 2,
                'production_line_id' => $infra['lines']['stamping']->id,
                'quantity' => 100 + $i * 30,
                'status' => 'in_produzione',
                'order_date' => now()->subDays(5 - $i),
                'priority' => 5 - $i,
                'customer' => 'Cliente Stamping ' . chr(67 + $i),
            ]);
            ProductionPhase::create([
                'production_order_id' => $order->id,
                'workstation_id' => $infra['workstations']['stamping_press']->id,
                'name' => 'Stampaggio Staffe',
                'estimated_duration' => 100 + $i * 40,
                'energy_consumption' => 90 + $i * 30,
                'is_completed' => false,
                'start_time' => now()->subDays(4 - $i)->setHour(8),
                'end_time' => now()->subDays(4 - $i)->setHour(11),
                'scheduled_start_time' => now()->subDays(4 - $i)->setHour(8),
                'scheduled_end_time' => now()->subDays(4 - $i)->setHour(11),
            ]);
        }
        $stampingAttesa = ProductionOrder::create([
            'internal_product_id' => $products['bracket']->id,
            'bom_id' => 2,
            'production_line_id' => $infra['lines']['stamping']->id,
            'quantity' => 20,
            'status' => 'in_attesa',
            'order_date' => now()->subDays(1),
            'priority' => 2,
            'customer' => 'Cliente Stamping Attesa',
        ]);
        ProductionPhase::create([
            'production_order_id' => $stampingAttesa->id,
            'workstation_id' => $infra['workstations']['stamping_press']->id,
            'name' => 'Stampaggio Staffe',
            'estimated_duration' => 40,
            'energy_consumption' => 20,
            'is_completed' => false,
            'start_time' => now()->addDay()->setHour(11),
            'end_time' => now()->addDay()->setHour(12),
            'scheduled_start_time' => null,
            'scheduled_end_time' => null,
        ]);

        // Ordini linea PLATE (come test, uno in produzione e uno in attesa)
        $plateProd = ProductionOrder::create([
            'internal_product_id' => $products['plate']->id,
            'bom_id' => 3,
            'production_line_id' => $infra['lines']['laser']->id,
            'quantity' => 8,
            'status' => 'in_produzione',
            'order_date' => now()->subDays(2),
            'priority' => 3,
            'customer' => 'Cliente Plate Prod',
        ]);
        ProductionPhase::create([
            'production_order_id' => $plateProd->id,
            'workstation_id' => $infra['workstations']['laser_cutter']->id,
            'name' => 'Taglio Lamiere',
            'estimated_duration' => 50,
            'energy_consumption' => 25,
            'is_completed' => false,
            'start_time' => now()->addDay()->setHour(13),
            'end_time' => now()->addDay()->setHour(14),
            'scheduled_start_time' => null,
            'scheduled_end_time' => null,
        ]);
        $plateAttesa = ProductionOrder::create([
            'internal_product_id' => $products['plate']->id,
            'bom_id' => 3,
            'production_line_id' => $infra['lines']['laser']->id,
            'quantity' => 4,
            'status' => 'in_attesa',
            'order_date' => now()->subDays(1),
            'priority' => 2,
            'customer' => 'Cliente Plate Attesa',
        ]);
        ProductionPhase::create([
            'production_order_id' => $plateAttesa->id,
            'workstation_id' => $infra['workstations']['laser_cutter']->id,
            'name' => 'Piegatura Lamiere',
            'estimated_duration' => 30,
            'energy_consumption' => 12,
            'is_completed' => false,
            'start_time' => now()->addDays(2)->setHour(11),
            'end_time' => now()->addDays(2)->setHour(11)->setMinute(45),
            'scheduled_start_time' => null,
            'scheduled_end_time' => null,
        ]);

        // Fasi extra per OEE e bottleneck
        $workstations = $infra['workstations'];
        $now = now();
        foreach (['laser_cutter', 'stamping_press'] as $wsKey) {
            $ws = $workstations[$wsKey];
            for ($i = 0; $i < 8; $i++) {
                $order = ProductionOrder::create([
                    'internal_product_id' => $wsKey === 'laser_cutter' ? $products['sheet']->id : $products['bracket']->id,
                    'bom_id' => $wsKey === 'laser_cutter' ? 1 : 2,
                    'production_line_id' => $ws->production_line_id,
                    'quantity' => rand(10, 60),
                    'status' => $i < 5 ? 'in_produzione' : 'in_attesa',
                    'order_date' => $now->copy()->subDays(rand(0, 14)),
                    'priority' => rand(1, 5),
                    'customer' => 'Cliente Extra ' . $ws->name . ' ' . $i,
                ]);
                $start = $now->copy()->subDays(rand(0, 14))->setHour(rand(8, 15))->setMinute(0);
                $duration = rand(30, 180); // 30-180 min
                $isCompleted = $i < 4;
                ProductionPhase::create([
                    'production_order_id' => $order->id,
                    'workstation_id' => $ws->id,
                    'name' => 'Fase Extra ' . $i,
                    'estimated_duration' => $duration,
                    'energy_consumption' => rand(10, 100),
                    'is_completed' => $isCompleted,
                    'start_time' => $start,
                    'end_time' => $isCompleted ? $start->copy()->addMinutes($duration) : null,
                    'scheduled_start_time' => $start->copy()->addDays(rand(-2, 2)),
                    'scheduled_end_time' => $start->copy()->addDays(rand(-2, 2))->addMinutes($duration),
                ]);
            }
        }
    }
    
    private function createTwinsForOrder(ProductionOrder $order, Warehouse $warehouse)
    {
        $co2PerUnit = ($order->phases()->sum('energy_consumption') * $order->internalProduct->emission_factor) / $order->quantity;
        for ($i = 0; $i < $order->quantity; $i++) {
            ProductTwin::create([
                'internal_product_id' => $order->internal_product_id,
                'current_warehouse_id' => $warehouse->id,
                'lifecycle_status' => 'in_stock',
                'co2_emissions_production' => $co2PerUnit,
                'co2_emissions_total' => $co2PerUnit,
            ]);
        }
    }
    
    private function createMovements(array $warehouses)
    {
        $products = [
            InternalProduct::firstWhere('code', 'LAM-F-100x200'), // sheet
            InternalProduct::firstWhere('code', 'STA-A-001'),      // bracket
            InternalProduct::firstWhere('code', 'LAM-P-50x150'),  // plate
        ];

        $carichi = [120, 80, 60];
        foreach ($products as $i => $product) {
            // Carico materie prime
            $carico = $carichi[$i];
            $twinsCarico = ProductTwin::factory()->count($carico)->create([
                'internal_product_id' => $product->id,
                'current_warehouse_id' => $warehouses['raw']->id,
                'lifecycle_status' => 'in_stock',
            ]);
            $caricoMovement = InventoryMovement::create([
                'movement_type' => 'carico',
                'internal_product_id' => $product->id,
                'to_warehouse_id' => $warehouses['raw']->id,
                'quantity' => $twinsCarico->count(),
                'distance_km' => 100 + $i * 10,
                'transport_mode' => 'camion',
                'note' => 'Carico materie prime ' . $product->name
            ]);
            $caricoMovement->productTwins()->attach($twinsCarico->pluck('id'));

            // Trasferimento a prodotti finiti (muovo metà del carico)
            $twinsToFinished = ProductTwin::where('internal_product_id', $product->id)
                ->where('current_warehouse_id', $warehouses['raw']->id)
                ->whereDoesntHave('inventoryMovements', function($q) use ($warehouses) {
                    $q->where('movement_type', 'trasferimento')
                      ->where('to_warehouse_id', $warehouses['finished']->id);
                })
                ->take(intdiv($carico, 2))->get();
            if ($twinsToFinished->count() > 0) {
                ProductTwin::whereIn('id', $twinsToFinished->pluck('id'))->update(['current_warehouse_id' => $warehouses['finished']->id]);
                $transferToFinished = InventoryMovement::create([
                    'movement_type' => 'trasferimento',
                    'internal_product_id' => $product->id,
                    'from_warehouse_id' => $warehouses['raw']->id,
                    'to_warehouse_id' => $warehouses['finished']->id,
                    'quantity' => $twinsToFinished->count(),
                    'note' => 'Trasferimento ' . $product->name . ' a prodotti finiti'
                ]);
                $transferToFinished->productTwins()->attach($twinsToFinished->pluck('id'));
            }

            // Vendita/Spedizione a Milano (muovo metà di quanto c'è in prodotti finiti)
            $twinsToMilan = ProductTwin::where('internal_product_id', $product->id)
                ->where('current_warehouse_id', $warehouses['finished']->id)
                ->whereDoesntHave('inventoryMovements', function($q) use ($warehouses) {
                    $q->where('movement_type', 'trasferimento')
                      ->where('to_warehouse_id', $warehouses['milan']->id);
                })
                ->take(intdiv($twinsToFinished->count(), 2))->get();
            if ($twinsToMilan->count() > 0) {
                ProductTwin::whereIn('id', $twinsToMilan->pluck('id'))->update(['current_warehouse_id' => $warehouses['milan']->id]);
                $transferToMilan = InventoryMovement::create([
                    'movement_type' => 'trasferimento',
                    'internal_product_id' => $product->id,
                    'from_warehouse_id' => $warehouses['finished']->id,
                    'to_warehouse_id' => $warehouses['milan']->id,
                    'quantity' => $twinsToMilan->count(),
                    'distance_km' => 45,
                    'transport_mode' => 'camion',
                    'note' => 'Spedizione ' . $product->name . ' a negozio Milano'
                ]);
                $transferToMilan->productTwins()->attach($twinsToMilan->pluck('id'));
            }

            // Reso da Milano a materie prime (muovo metà di quanto c'è in milan)
            $twinsReturn = ProductTwin::where('internal_product_id', $product->id)
                ->where('current_warehouse_id', $warehouses['milan']->id)
                ->whereDoesntHave('inventoryMovements', function($q) use ($warehouses) {
                    $q->where('movement_type', 'trasferimento')
                      ->where('to_warehouse_id', $warehouses['raw']->id);
                })
                ->take(intdiv($twinsToMilan->count(), 2))->get();
            if ($twinsReturn->count() > 0) {
                ProductTwin::whereIn('id', $twinsReturn->pluck('id'))->update(['current_warehouse_id' => $warehouses['raw']->id]);
                $movementType = $warehouses['milan']->is_final_destination ? 'reso' : 'trasferimento';
                $returnTransfer = InventoryMovement::create([
                    'movement_type' => $movementType,
                    'internal_product_id' => $product->id,
                    'from_warehouse_id' => $warehouses['milan']->id,
                    'to_warehouse_id' => $warehouses['raw']->id,
                    'quantity' => $twinsReturn->count(),
                    'distance_km' => 45,
                    'transport_mode' => 'camion',
                    'note' => 'Reso ' . $product->name . ' da Milano a materie prime'
                ]);
                $returnTransfer->productTwins()->attach($twinsReturn->pluck('id'));
            }

            // Scarico (vendita o consumo): solo dal magazzino finale
            $finalWarehouse = $warehouses['milan'];
            $twinsToUnload = ProductTwin::where('internal_product_id', $product->id)
                ->where('current_warehouse_id', $finalWarehouse->id)
                ->whereDoesntHave('inventoryMovements', function($q) {
                    $q->where('movement_type', 'scarico');
                })
                ->take(intdiv($carico, 3))->get();
            if ($twinsToUnload->count() > 0) {
                ProductTwin::whereIn('id', $twinsToUnload->pluck('id'))->update(['current_warehouse_id' => null, 'lifecycle_status' => 'sold']);
                $unloadMovement = InventoryMovement::create([
                    'movement_type' => 'scarico',
                    'internal_product_id' => $product->id,
                    'from_warehouse_id' => $finalWarehouse->id,
                    'quantity' => $twinsToUnload->count(),
                    'note' => 'Scarico (vendita/consumo) ' . $product->name
                ]);
                $unloadMovement->productTwins()->attach($twinsToUnload->pluck('id'));
            }
        }

        // DEBUG: log dettagliato per ogni magazzino
        foreach ($warehouses as $key => $warehouse) {
            $in = \App\Models\InventoryMovement::where('to_warehouse_id', $warehouse->id)->count();
            $out = \App\Models\InventoryMovement::where('from_warehouse_id', $warehouse->id)->count();
            $twins = \App\Models\ProductTwin::where('current_warehouse_id', $warehouse->id)->count();
            echo "[DEBUG] Warehouse {$warehouse->name} (ID: {$warehouse->id}) - Entrata: $in, Uscita: $out, Twin: $twins\n";
            $movs = \App\Models\InventoryMovement::where(function($q) use ($warehouse) {
                $q->where('to_warehouse_id', $warehouse->id)
                  ->orWhere('from_warehouse_id', $warehouse->id);
            })->orderBy('id')->get();
            foreach ($movs as $mov) {
                $twinIds = $mov->productTwins->pluck('id')->implode(',');
                echo "  - [{$mov->movement_type}] ID:{$mov->id} Qta:{$mov->quantity} FROM:{$mov->from_warehouse_id} TO:{$mov->to_warehouse_id} Twin:[$twinIds]\n";
            }
        }
    }

    private function createFeedback(array $workstations)
    {
        OperatorFeedback::create([
            'titolo' => 'Aumentare velocità taglio laser',
            'descrizione' => 'La velocità di taglio per la Lamiera Forata 100x200 potrebbe essere aumentata del 5% senza compromettere la qualità.',
            'status' => 'pending',
            'metadata' => [
                'workstation' => $workstations['laser_cutter']->name,
                'operator' => 'Mario Rossi',
                'urgency' => 'medium'
            ]
        ]);
        OperatorFeedback::create([
            'titolo' => 'Rumore anomalo pressa',
            'descrizione' => 'La Pressa #1 emette un rumore anomalo durante la fase di stampaggio delle staffe angolari.',
            'status' => 'pending',
            'metadata' => [
                'workstation' => $workstations['stamping_press']->name,
                'operator' => 'Luca Bianchi',
                'urgency' => 'high'
            ]
        ]);
    }
} 