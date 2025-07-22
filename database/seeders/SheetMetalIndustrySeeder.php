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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SheetMetalIndustrySeeder extends Seeder
{
    /**
     * Seed the application's database with realistic sheet metal industry data.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('🗑️  Clearing existing data...');
            $this->clearExistingData();

            $this->command->info('🏭 Creating warehouses...');
            $warehouses = $this->createWarehouses();

            $this->command->info('🔧 Creating internal products...');
            $products = $this->createInternalProducts();

            $this->command->info('📋 Creating BOMs...');
            $boms = $this->createBoms($products);

            $this->command->info('🏭 Creating production lines and workstations...');
            $productionData = $this->createProductionInfrastructure();

            $this->command->info('📦 Creating production orders and twins...');
            $this->createProductionOrdersAndTwins($boms, $productionData, $warehouses, $products);

            $this->command->info('🚚 Creating inventory movements...');
            $this->createInventoryMovements($warehouses, $products);

            $this->command->info('✅ Demo data created successfully!');
        });
    }

    private function clearExistingData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        ProductTwin::truncate();
        InventoryMovement::truncate();
        ProductionPhase::truncate();
        ProductionOrder::truncate();
        Bom::truncate();
        InternalProduct::truncate();
        Warehouse::truncate();
        Workstation::truncate();
        ProductionLine::truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function createWarehouses(): array
    {
        return [
            'raw_materials' => Warehouse::create(['name' => 'Magazzino Materie Prime', 'type' => 'magazzino']),
            'finished_goods' => Warehouse::create(['name' => 'Magazzino Prodotti Finiti', 'type' => 'magazzino']),
            'milan_store' => Warehouse::create(['name' => 'Showroom Milano', 'type' => 'negozio', 'is_final_destination' => true]),
            'rome_store' => Warehouse::create(['name' => 'Negozio Roma', 'type' => 'negozio', 'is_final_destination' => true]),
            'supplier_steel' => Warehouse::create(['name' => 'Fornitore Acciaio Inox', 'type' => 'fornitore']),
        ];
    }

    private function createInternalProducts(): array
    {
        return [
            'perforated_panel_steel' => InternalProduct::create([
                'name' => 'Pannello Forato Acciaio Inox 1000x500mm',
                'code' => 'PF-SS-1000x500',
                'description' => 'Pannello forato in acciaio inox AISI 304, fori Ø5mm passo 8mm',
                'unit_of_measure' => 'pz',
                'weight' => 12.5,
                'emission_factor' => 0.15,
            ]),
            'stamped_bracket' => InternalProduct::create([
                'name' => 'Staffa Stampata L-Shape 150mm',
                'code' => 'ST-L150',
                'description' => 'Staffa stampata a L in acciaio zincato, spessore 3mm',
                'unit_of_measure' => 'pz',
                'weight' => 0.8,
                'emission_factor' => 0.18,
            ]),
        ];
    }

    private function createBoms(array $products): array
    {
        return [
            Bom::create(['internal_product_id' => $products['perforated_panel_steel']->id, 'internal_code' => 'PF-SS-1000x500']),
            Bom::create(['internal_product_id' => $products['stamped_bracket']->id, 'internal_code' => 'ST-L150']),
        ];
    }

    private function createProductionInfrastructure(): array
    {
        $laserLine = ProductionLine::create(['name' => 'Linea Taglio Laser', 'description' => 'Linea per taglio e foratura']);
        $stampingLine = ProductionLine::create(['name' => 'Linea Stampaggio', 'description' => 'Linea per stampaggio e piegatura']);

        return [
            'lines' => ['laser' => $laserLine, 'stamping' => $stampingLine],
            'workstations' => [
                'laser_cutter' => Workstation::create(['production_line_id' => $laserLine->id, 'name' => 'Taglio Laser A', 'batch_size' => 10, 'capacity' => 8]),
                'punching_machine' => Workstation::create(['production_line_id' => $laserLine->id, 'name' => 'Punzonatrice B', 'batch_size' => 20, 'capacity' => 8]),
                'stamping_press' => Workstation::create(['production_line_id' => $stampingLine->id, 'name' => 'Pressa Stampaggio C', 'batch_size' => 50, 'capacity' => 8]),
                'bending_machine' => Workstation::create(['production_line_id' => $stampingLine->id, 'name' => 'Piegatrice D', 'batch_size' => 30, 'capacity' => 8]),
            ]
        ];
    }

    private function createProductionOrdersAndTwins(array $boms, array $productionData, array $warehouses, array $products): void
    {
        $orders = [
            [
                'bom' => $boms[0], 'quantity' => 25, 'line' => $productionData['lines']['laser'],
                'phases' => [
                    ['name' => 'Taglio Laser', 'energy' => 8.5, 'duration' => 60, 'workstation' => $productionData['workstations']['laser_cutter']],
                    ['name' => 'Foratura', 'energy' => 12.0, 'duration' => 90, 'workstation' => $productionData['workstations']['punching_machine']],
                ],
            ],
            [
                'bom' => $boms[1], 'quantity' => 100, 'line' => $productionData['lines']['stamping'],
                'phases' => [
                    ['name' => 'Stampaggio', 'energy' => 4.8, 'duration' => 30, 'workstation' => $productionData['workstations']['stamping_press']],
                    ['name' => 'Piegatura', 'energy' => 2.1, 'duration' => 45, 'workstation' => $productionData['workstations']['bending_machine']],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            $order = ProductionOrder::create([
                'bom_id' => $orderData['bom']->id,
                'internal_product_id' => $orderData['bom']->internal_product_id,
                'production_line_id' => $orderData['line']->id,
                'quantity' => $orderData['quantity'],
                'status' => 'completato',
                'order_date' => now()->subDays(rand(5, 20)),
                'priority' => rand(1, 5),
            ]);

            $totalEnergy = 0;
            foreach ($orderData['phases'] as $phaseData) {
                ProductionPhase::create([
                    'production_order_id' => $order->id,
                    'workstation_id' => $phaseData['workstation']->id,
                    'name' => $phaseData['name'],
                    'energy_consumption' => $phaseData['energy'],
                    'estimated_duration' => $phaseData['duration'],
                    'is_completed' => true,
                ]);
                $totalEnergy += $phaseData['energy'];
            }

            $product = InternalProduct::find($orderData['bom']->internal_product_id);
            $co2PerUnit = ($totalEnergy * $product->emission_factor) / $order->quantity;

            for ($i = 0; $i < $order->quantity; $i++) {
                ProductTwin::create([
                    'internal_product_id' => $product->id,
                    'current_warehouse_id' => $warehouses['finished_goods']->id,
                    'lifecycle_status' => 'in_stock',
                    'co2_emissions_production' => $co2PerUnit,
                    'co2_emissions_total' => $co2PerUnit,
                ]);
            }
        }
    }

    private function createInventoryMovements(array $warehouses, array $products): void
    {
        $steelTwins = ProductTwin::whereHas('internalProduct', fn($q) => $q->where('code', 'PF-SS-1000x500'))->get();
        if ($steelTwins->isNotEmpty()) {
            $movement = InventoryMovement::create([
                'movement_type' => 'transfer',
                'from_warehouse_id' => $warehouses['finished_goods']->id,
                'to_warehouse_id' => $warehouses['milan_store']->id,
                'distance_km' => 150,
                'transport_mode' => 'camion',
            ]);
            $movement->productTwins()->attach($steelTwins->pluck('id'));
        }
    }
} 