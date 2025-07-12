<?php

namespace Database\Seeders;

use App\Models\InventoryMovement;
use App\Models\LogisticProduct;
use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ottengo prodotti e magazzini esistenti
        $products = LogisticProduct::all();
        $warehouses = Warehouse::all();
        
        if ($products->isEmpty() || $warehouses->isEmpty()) {
            $this->command->error('❌ Assicurati di aver eseguito prima LogisticProductSeeder e WarehouseSeeder');
            return;
        }

        $fornitori = $warehouses->where('tipo', 'fornitore');
        $magazzini = $warehouses->where('tipo', 'magazzino');
        $negozi = $warehouses->where('tipo', 'negozio');

        $movements = [];
        $movementCounter = 0;

        // 1. Carichi iniziali dai fornitori ai magazzini centrali
        foreach ($products as $product) {
            $fornitore = $fornitori->random();
            $magazzinoCentrale = $magazzini->where('nome', 'like', '%Centrale%')->first() ?? $magazzini->random();
            
            $movements[] = [
                'logistic_product_id' => $product->id,
                'from_warehouse_id' => null,
                'to_warehouse_id' => $magazzinoCentrale->id,
                'movement_type' => 'carico',
                'quantita' => rand(50, 500),
                'note' => "Carico iniziale da {$fornitore->nome}",
                'production_order_id' => null,
                'origine_automatica' => false,
                'created_at' => now()->subDays(rand(30, 60))
            ];
            $movementCounter++;
        }

        // 2. Riordini da fornitori (più recenti)
        foreach ($products->random(10) as $product) {
            $fornitore = $fornitori->random();
            $magazzino = $magazzini->random();
            
            $movements[] = [
                'logistic_product_id' => $product->id,
                'from_warehouse_id' => null,
                'to_warehouse_id' => $magazzino->id,
                'movement_type' => 'carico',
                'quantita' => rand(20, 200),
                'note' => "Riordino da {$fornitore->nome}",
                'production_order_id' => null,
                'origine_automatica' => false,
                'created_at' => now()->subDays(rand(1, 15))
            ];
            $movementCounter++;
        }

        // 3. Trasferimenti tra magazzini
        foreach ($products->random(8) as $product) {
            $magazzinoDa = $magazzini->random();
            $magazziniDisponibili = $magazzini->where('id', '!=', $magazzinoDa->id);
            if ($magazziniDisponibili->isNotEmpty()) {
                $magazzinoPer = $magazziniDisponibili->random();
                
                $movements[] = [
                    'logistic_product_id' => $product->id,
                    'from_warehouse_id' => $magazzinoDa->id,
                    'to_warehouse_id' => $magazzinoPer->id,
                    'movement_type' => 'trasferimento',
                    'quantita' => rand(10, 100),
                    'note' => "Trasferimento da {$magazzinoDa->nome} a {$magazzinoPer->nome}",
                    'production_order_id' => null,
                    'origine_automatica' => false,
                    'created_at' => now()->subDays(rand(5, 25))
                ];
                $movementCounter++;
            }
        }

        // 4. Uscite verso negozi
        foreach ($products->random(12) as $product) {
            $magazzino = $magazzini->random();
            $negozio = $negozi->random();
            
            $movements[] = [
                'logistic_product_id' => $product->id,
                'from_warehouse_id' => $magazzino->id,
                'to_warehouse_id' => $negozio->id,
                'movement_type' => 'trasferimento',
                'quantita' => rand(5, 50),
                'note' => "Rifornimento per {$negozio->nome}",
                'production_order_id' => null,
                'origine_automatica' => false,
                'created_at' => now()->subDays(rand(1, 10))
            ];
            $movementCounter++;
        }

        // 5. Scarichi per vendite dai negozi
        foreach ($negozi as $negozio) {
            $prodottiVenduti = $products->random(rand(3, 8));
            foreach ($prodottiVenduti as $product) {
                $movements[] = [
                    'logistic_product_id' => $product->id,
                    'from_warehouse_id' => $negozio->id,
                    'to_warehouse_id' => null,
                    'movement_type' => 'scarico',
                    'quantita' => rand(1, 20),
                    'note' => "Vendita al dettaglio",
                    'production_order_id' => null,
                    'origine_automatica' => false,
                    'created_at' => now()->subDays(rand(0, 7))
                ];
                $movementCounter++;
            }
        }

        // 6. Movimenti automatici da produzione (simulati)
        $prodottiFiniti = $products->where('codice', 'like', 'FIN-%');
        foreach ($prodottiFiniti as $product) {
            $magazzinoProduzione = $magazzini->where('nome', 'like', '%Prodotti Finiti%')->first() ?? $magazzini->random();
            
            $movements[] = [
                'logistic_product_id' => $product->id,
                'from_warehouse_id' => null,
                'to_warehouse_id' => $magazzinoProduzione->id,
                'movement_type' => 'carico',
                'quantita' => rand(10, 100),
                'note' => 'Prodotto completato - carico automatico',
                'production_order_id' => rand(1000, 9999), // Simulo ID ordini produzione
                'origine_automatica' => true,
                'created_at' => now()->subDays(rand(2, 20))
            ];
            $movementCounter++;
        }

        // 7. Scarichi per scadenza/danneggiamento
        foreach ($products->random(5) as $product) {
            $magazzino = $magazzini->random();
            
            $movements[] = [
                'logistic_product_id' => $product->id,
                'from_warehouse_id' => $magazzino->id,
                'to_warehouse_id' => null,
                'movement_type' => 'scarico',
                'quantita' => rand(1, 10),
                'note' => 'Prodotto scaduto/danneggiato',
                'production_order_id' => null,
                'origine_automatica' => false,
                'created_at' => now()->subDays(rand(1, 30))
            ];
            $movementCounter++;
        }

        // 8. Movimenti recenti per test
        foreach ($products->random(15) as $product) {
            $tipoMovimento = collect(['carico', 'scarico', 'trasferimento'])->random();
            $magazzini_disponibili = $magazzini;
            
            $movimento = [
                'logistic_product_id' => $product->id,
                'movement_type' => $tipoMovimento,
                'quantita' => rand(1, 50),
                'production_order_id' => null,
                'origine_automatica' => false,
                'created_at' => now()->subDays(rand(0, 3))
            ];

            switch ($tipoMovimento) {
                case 'carico':
                    $movimento['from_warehouse_id'] = null;
                    $movimento['to_warehouse_id'] = $magazzini_disponibili->random()->id;
                    $movimento['note'] = 'Carico recente';
                    break;
                    
                case 'scarico':
                    $movimento['from_warehouse_id'] = $magazzini_disponibili->random()->id;
                    $movimento['to_warehouse_id'] = null;
                    $movimento['note'] = 'Scarico recente';
                    break;
                    
                case 'trasferimento':
                    $magazzinoDa = $magazzini_disponibili->random();
                    $magazziniPer = $magazzini_disponibili->where('id', '!=', $magazzinoDa->id);
                    if ($magazziniPer->isNotEmpty()) {
                        $movimento['from_warehouse_id'] = $magazzinoDa->id;
                        $movimento['to_warehouse_id'] = $magazziniPer->random()->id;
                        $movimento['note'] = 'Trasferimento recente';
                    }
                    break;
            }
            
            $movements[] = $movimento;
            $movementCounter++;
        }

        // Inserisco tutti i movimenti
        foreach ($movements as $movement) {
            InventoryMovement::create($movement);
        }

        $this->command->info("✅ Creati {$movementCounter} movimenti di inventario");
        $this->command->info("   - Carichi iniziali e riordini");
        $this->command->info("   - Trasferimenti tra magazzini");
        $this->command->info("   - Rifornimenti negozi");
        $this->command->info("   - Vendite e scarichi");
        $this->command->info("   - Movimenti automatici da produzione");
    }
}
