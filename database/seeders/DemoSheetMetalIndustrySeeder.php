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
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSheetMetalIndustrySeeder extends Seeder
{
    // Rimosso WithoutModelEvents per permettere la generazione UUID

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

            $this->command->info('🏭 Creating production lines...');
            $productionLines = $this->createProductionLines();

            $this->command->info('📦 Creating production orders and twins...');
            $this->createProductionOrdersAndTwins($boms, $productionLines, $warehouses, $products);

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
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function createWarehouses(): array
    {
        return [
            'raw_materials' => Warehouse::create([
                'name' => 'Magazzino Materie Prime',
                'type' => 'magazzino',
                'is_final_destination' => false,
            ]),
            'finished_goods' => Warehouse::create([
                'name' => 'Magazzino Prodotti Finiti',
                'type' => 'magazzino', 
                'is_final_destination' => false,
            ]),
            'milan_store' => Warehouse::create([
                'name' => 'Showroom Milano',
                'type' => 'negozio',
                'is_final_destination' => true,
            ]),
            'rome_store' => Warehouse::create([
                'name' => 'Negozio Roma',
                'type' => 'negozio',
                'is_final_destination' => true,
            ]),
            'supplier_steel' => Warehouse::create([
                'name' => 'Fornitore Acciaio Inox',
                'type' => 'fornitore',
                'is_final_destination' => false,
            ]),
            'supplier_aluminum' => Warehouse::create([
                'name' => 'Fornitore Alluminio',
                'type' => 'fornitore',
                'is_final_destination' => false,
            ]),
        ];
    }

    private function createInternalProducts(): array
    {
        return [
            // Prodotti finiti
            'perforated_panel_steel' => InternalProduct::create([
                'name' => 'Pannello Forato Acciaio Inox 1000x500mm',
                'code' => 'PF-SS-1000x500',
                'description' => 'Pannello forato in acciaio inox AISI 304, fori Ø5mm passo 8mm',
                'unit_of_measure' => 'pz',
                'weight' => 12.5,
                'materials' => ['acciaio_inox_304'],
                'emission_factor' => 0.15, // kgCO2/kWh
                'co2_avoided' => 2.3,
                'expected_lifespan_days' => 7300, // 20 anni
                'is_recyclable' => true,
            ]),
            'perforated_panel_aluminum' => InternalProduct::create([
                'name' => 'Pannello Forato Alluminio 800x400mm',
                'code' => 'PF-AL-800x400',
                'description' => 'Pannello forato in alluminio anodizzato, fori Ø6mm passo 10mm',
                'unit_of_measure' => 'pz',
                'weight' => 4.2,
                'materials' => ['alluminio_6061'],
                'emission_factor' => 0.12,
                'co2_avoided' => 1.8,
                'expected_lifespan_days' => 5475, // 15 anni
                'is_recyclable' => true,
            ]),
            'stamped_bracket' => InternalProduct::create([
                'name' => 'Staffa Stampata L-Shape 150mm',
                'code' => 'ST-L150',
                'description' => 'Staffa stampata a L in acciaio zincato, spessore 3mm',
                'unit_of_measure' => 'pz',
                'weight' => 0.8,
                'materials' => ['acciaio_zincato'],
                'emission_factor' => 0.18,
                'co2_avoided' => 0.4,
                'expected_lifespan_days' => 3650, // 10 anni
                'is_recyclable' => true,
            ]),
            'decorative_screen' => InternalProduct::create([
                'name' => 'Schermo Decorativo Ottone 600x300mm',
                'code' => 'SD-BR-600x300',
                'description' => 'Schermo decorativo in ottone con pattern geometrico',
                'unit_of_measure' => 'pz',
                'weight' => 2.1,
                'materials' => ['ottone'],
                'emission_factor' => 0.22,
                'co2_avoided' => 1.1,
                'expected_lifespan_days' => 9125, // 25 anni
                'is_recyclable' => true,
            ]),
            'ventilation_grille' => InternalProduct::create([
                'name' => 'Griglia Ventilazione Ø200mm',
                'code' => 'GV-200',
                'description' => 'Griglia per ventilazione circolare in alluminio',
                'unit_of_measure' => 'pz',
                'weight' => 0.6,
                'materials' => ['alluminio_6061'],
                'emission_factor' => 0.10,
                'co2_avoided' => 0.3,
                'expected_lifespan_days' => 5475, // 15 anni
                'is_recyclable' => true,
            ]),
            // Materie prime
            'steel_sheet_304' => InternalProduct::create([
                'name' => 'Lamiera Acciaio Inox AISI 304',
                'code' => 'MP-SS304-2000x1000x2',
                'description' => 'Lamiera in acciaio inox AISI 304, 2000x1000mm spessore 2mm',
                'unit_of_measure' => 'kg',
                'weight' => 31.4,
                'materials' => ['acciaio_inox_304'],
                'emission_factor' => 0.05,
                'co2_avoided' => 0.0,
                'expected_lifespan_days' => null,
                'is_recyclable' => true,
            ]),
            'aluminum_sheet' => InternalProduct::create([
                'name' => 'Lamiera Alluminio 6061',
                'code' => 'MP-AL6061-1500x750x1.5',
                'description' => 'Lamiera in lega di alluminio 6061, 1500x750mm spessore 1.5mm',
                'unit_of_measure' => 'kg',
                'weight' => 3.0,
                'materials' => ['alluminio_6061'],
                'emission_factor' => 0.03,
                'co2_avoided' => 0.0,
                'expected_lifespan_days' => null,
                'is_recyclable' => true,
            ]),
        ];
    }

    private function createBoms(array $products): array
    {
        return [
            Bom::create([
                'internal_product_id' => $products['perforated_panel_steel']->id,
                'internal_code' => 'PF-SS-1000x500',
                'materials' => [
                    ['material' => 'Lamiera Acciaio Inox AISI 304', 'quantity' => 1, 'unit' => 'pz'],
                    ['material' => 'Vernice protettiva', 'quantity' => 0.2, 'unit' => 'kg'],
                ],
            ]),
            Bom::create([
                'internal_product_id' => $products['perforated_panel_aluminum']->id,
                'internal_code' => 'PF-AL-800x400',
                'materials' => [
                    ['material' => 'Lamiera Alluminio 6061', 'quantity' => 1, 'unit' => 'pz'],
                    ['material' => 'Trattamento anodizzazione', 'quantity' => 1, 'unit' => 'trattamento'],
                ],
            ]),
            Bom::create([
                'internal_product_id' => $products['stamped_bracket']->id,
                'internal_code' => 'ST-L150',
                'materials' => [
                    ['material' => 'Lamiera Acciaio Zincato', 'quantity' => 0.8, 'unit' => 'kg'],
                    ['material' => 'Zincatura', 'quantity' => 1, 'unit' => 'trattamento'],
                ],
            ]),
            Bom::create([
                'internal_product_id' => $products['decorative_screen']->id,
                'internal_code' => 'SD-BR-600x300',
                'materials' => [
                    ['material' => 'Lamiera Ottone', 'quantity' => 2.1, 'unit' => 'kg'],
                    ['material' => 'Lucidatura', 'quantity' => 1, 'unit' => 'trattamento'],
                ],
            ]),
            Bom::create([
                'internal_product_id' => $products['ventilation_grille']->id,
                'internal_code' => 'GV-200',
                'materials' => [
                    ['material' => 'Lamiera Alluminio 6061', 'quantity' => 0.6, 'unit' => 'kg'],
                    ['material' => 'Assemblaggio componenti', 'quantity' => 1, 'unit' => 'set'],
                ],
            ]),
        ];
    }

    private function createProductionLines(): array
    {
        return [
            'laser_cutting' => ProductionLine::create([
                'name' => 'Linea Taglio Laser',
                'description' => 'Linea di produzione per taglio laser di precisione',
                'status' => 'active',
            ]),
            'punching' => ProductionLine::create([
                'name' => 'Linea Punzonatura',
                'description' => 'Linea di produzione per punzonatura e foratura',
                'status' => 'active',
            ]),
            'stamping' => ProductionLine::create([
                'name' => 'Linea Stampaggio',
                'description' => 'Linea di produzione per stampaggio e piegatura',
                'status' => 'active',
            ]),
        ];
    }

    private function createProductionOrdersAndTwins(array $boms, array $productionLines, array $warehouses, array $products): void
    {
        $orders = [
            [
                'bom' => $boms[0], // Pannello Forato Acciaio
                'quantity' => 25,
                'line' => $productionLines['punching'],
                'phases' => [
                    ['name' => 'Taglio Laser', 'energy' => 8.5],
                    ['name' => 'Foratura', 'energy' => 12.0],
                    ['name' => 'Finitura', 'energy' => 4.2],
                ],
            ],
            [
                'bom' => $boms[1], // Pannello Forato Alluminio
                'quantity' => 40,
                'line' => $productionLines['punching'],
                'phases' => [
                    ['name' => 'Taglio Laser', 'energy' => 6.8],
                    ['name' => 'Foratura', 'energy' => 9.5],
                    ['name' => 'Anodizzazione', 'energy' => 5.1],
                ],
            ],
            [
                'bom' => $boms[2], // Staffa Stampata
                'quantity' => 100,
                'line' => $productionLines['stamping'],
                'phases' => [
                    ['name' => 'Taglio', 'energy' => 2.1],
                    ['name' => 'Stampaggio', 'energy' => 4.8],
                    ['name' => 'Zincatura', 'energy' => 1.5],
                ],
            ],
            [
                'bom' => $boms[3], // Schermo Decorativo
                'quantity' => 15,
                'line' => $productionLines['laser_cutting'],
                'phases' => [
                    ['name' => 'Taglio Laser Precision', 'energy' => 15.2],
                    ['name' => 'Incisione Pattern', 'energy' => 8.7],
                    ['name' => 'Lucidatura', 'energy' => 3.8],
                ],
            ],
            [
                'bom' => $boms[4], // Griglia Ventilazione
                'quantity' => 60,
                'line' => $productionLines['punching'],
                'phases' => [
                    ['name' => 'Taglio Circolare', 'energy' => 3.2],
                    ['name' => 'Foratura Alette', 'energy' => 5.1],
                    ['name' => 'Assemblaggio', 'energy' => 1.8],
                ],
            ],
        ];

        foreach ($orders as $orderData) {
            // Crea ordine di produzione
            $order = ProductionOrder::create([
                'bom_id' => $orderData['bom']->id,
                'internal_product_id' => $orderData['bom']->internal_product_id,
                'production_line_id' => $orderData['line']->id,
                'customer' => 'Cliente Demo ' . rand(100, 999),
                'order_date' => now()->subDays(rand(5, 20)),
                'quantity' => $orderData['quantity'],
                'status' => 'completato',
                'priority' => rand(1, 5),
                'notes' => 'Ordine completato - Demo data',
            ]);

            // Crea fasi di produzione
            $totalEnergy = 0;
            foreach ($orderData['phases'] as $phaseData) {
                ProductionPhase::create([
                    'production_order_id' => $order->id,
                    'name' => $phaseData['name'],
                    'energy_consumption' => $phaseData['energy'],
                    'estimated_duration' => rand(30, 180),
                    'operator' => 'Operatore Demo',
                    'is_completed' => true,
                ]);
                $totalEnergy += $phaseData['energy'];
            }

            // Calcola CO2 di produzione
            $product = InternalProduct::find($orderData['bom']->internal_product_id);
            $totalCo2Production = $totalEnergy * $product->emission_factor;
            $co2PerUnit = $totalCo2Production / $order->quantity;

            // Crea ProductTwin per ogni pezzo prodotto
            for ($i = 0; $i < $order->quantity; $i++) {
                ProductTwin::create([
                    'internal_product_id' => $product->id,
                    'current_warehouse_id' => $warehouses['finished_goods']->id,
                    'lifecycle_status' => 'in_stock',
                    'co2_emissions_production' => $co2PerUnit,
                    'co2_emissions_logistics' => 0,
                    'co2_emissions_total' => $co2PerUnit,
                                         'metadata' => [
                         'production_order_id' => $order->id,
                         'production_date' => $order->order_date->format('Y-m-d'),
                         'batch_number' => 'BATCH-' . $order->id . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                     ],
                ]);
            }
        }
    }

    private function createInventoryMovements(array $warehouses, array $products): void
    {
        // Caricamento materie prime dal fornitore
        $movements = [
            [
                'type' => 'load',
                'product' => $products['steel_sheet_304'],
                'to_warehouse' => $warehouses['raw_materials'],
                'quantity' => 50,
                'distance' => 80,
                'transport' => 'camion',
                'note' => 'Carico lamiere acciaio inox dal fornitore',
            ],
            [
                'type' => 'load', 
                'product' => $products['aluminum_sheet'],
                'to_warehouse' => $warehouses['raw_materials'],
                'quantity' => 120,
                'distance' => 120,
                'transport' => 'camion',
                'note' => 'Carico lamiere alluminio dal fornitore',
            ],
        ];

        // Trasferimenti ai negozi (usando ProductTwin esistenti)
        $finishedTwins = ProductTwin::where('current_warehouse_id', $warehouses['finished_goods']->id)
            ->where('lifecycle_status', 'in_stock')
            ->get();

        if ($finishedTwins->count() > 0) {
            // Trasferimento a Milano (pannelli acciaio)
            $steelTwins = $finishedTwins->filter(function($twin) use ($products) {
                return $twin->internal_product_id === $products['perforated_panel_steel']->id;
            })->take(8);
            
            if ($steelTwins->count() > 0) {
                $movements[] = [
                    'type' => 'transfer',
                    'from_warehouse' => $warehouses['finished_goods'],
                    'to_warehouse' => $warehouses['milan_store'],
                    'twins' => $steelTwins,
                    'distance' => 150,
                    'transport' => 'camion',
                    'note' => 'Trasferimento pannelli acciaio a Milano',
                ];
            }

            // Trasferimento a Roma (mix di prodotti)
            $mixedTwins = $finishedTwins->filter(function($twin) use ($products) {
                return in_array($twin->internal_product_id, [
                    $products['perforated_panel_aluminum']->id, 
                    $products['stamped_bracket']->id, 
                    $products['ventilation_grille']->id
                ]);
            })->take(12);
            
            if ($mixedTwins->count() > 0) {
                $movements[] = [
                    'type' => 'transfer',
                    'from_warehouse' => $warehouses['finished_goods'],
                    'to_warehouse' => $warehouses['rome_store'],
                    'twins' => $mixedTwins,
                    'distance' => 280,
                    'transport' => 'camion',
                    'note' => 'Trasferimento prodotti vari a Roma',
                ];
            }

            // Scarico per installazione (schermi decorativi)
            $decorativeTwins = $finishedTwins->filter(function($twin) use ($products) {
                return $twin->internal_product_id === $products['decorative_screen']->id;
            })->take(3);
            
            if ($decorativeTwins->count() > 0) {
                $movements[] = [
                    'type' => 'unload',
                    'from_warehouse' => $warehouses['finished_goods'],
                    'twins' => $decorativeTwins,
                    'distance' => 25,
                    'transport' => 'camion',
                    'note' => 'Scarico per installazione cliente premium',
                ];
            }
        }

        // Crea i movimenti
        foreach ($movements as $movementData) {
            if ($movementData['type'] === 'load') {
                $this->createLoadMovement($movementData);
            } else {
                $this->createTransferOrUnloadMovement($movementData);
            }
        }
    }

    private function createLoadMovement(array $data): void
    {
        $movement = InventoryMovement::create([
            'movement_type' => $data['type'],
            'to_warehouse_id' => $data['to_warehouse']->id,
            'internal_product_id' => $data['product']->id,
            'quantity' => $data['quantity'],
            'distance_km' => $data['distance'],
            'transport_mode' => $data['transport'],
            'note' => $data['note'],
        ]);

        // L'observer creerà automaticamente i ProductTwin
    }

    private function createTransferOrUnloadMovement(array $data): void
    {
        $movement = InventoryMovement::create([
            'movement_type' => $data['type'],
            'from_warehouse_id' => $data['from_warehouse']->id,
            'to_warehouse_id' => $data['to_warehouse']->id ?? null,
            'distance_km' => $data['distance'],
            'transport_mode' => $data['transport'],
            'note' => $data['note'],
        ]);

        // Associa i ProductTwin specificati
        $twinIds = $data['twins']->pluck('id')->toArray();
        $movement->productTwins()->attach($twinIds);

        // L'observer aggiornerà automaticamente status e CO2
    }
}
