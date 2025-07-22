<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            // Fornitori
            [
                'name' => 'Molino San Giuseppe',
                'type' => 'fornitore'
            ],
            [
                'name' => 'Oleificio Toscano',
                'type' => 'fornitore'
            ],
            [
                'name' => 'Zuccherificio Nazionale',
                'type' => 'fornitore'
            ],
            [
                'name' => 'Caseificio Valle Verde',
                'type' => 'fornitore'
            ],
            [
                'name' => 'Fornitore Packaging SRL',
                'type' => 'fornitore'
            ],
            [
                'name' => 'Ingredienti & Co.',
                'type' => 'fornitore'
            ],
            
            // Magazzini centrali
            [
                'name' => 'Magazzino Centrale Nord',
                'type' => 'magazzino'
            ],
            [
                'name' => 'Magazzino Centrale Sud',
                'type' => 'magazzino'
            ],
            [
                'name' => 'Deposito Materie Prime',
                'type' => 'magazzino'
            ],
            [
                'name' => 'Deposito Prodotti Finiti',
                'type' => 'magazzino'
            ],
            [
                'name' => 'Magazzino Refrigerato',
                'type' => 'magazzino'
            ],
            
            // Negozi e punti vendita
            [
                'name' => 'Negozio Centro Storico',
                'type' => 'negozio',
                'is_final_destination' => true,
            ],
            [
                'name' => 'Punto Vendita Periferia',
                'type' => 'negozio',
                'is_final_destination' => true,
            ],
            [
                'name' => 'Outlet Factory Store',
                'type' => 'negozio',
                'is_final_destination' => true,
            ],
            [
                'name' => 'Corner Supermercato',
                'type' => 'negozio',
                'is_final_destination' => true,
            ],
            [
                'name' => 'Negozio Online (E-commerce)',
                'type' => 'negozio',
                'is_final_destination' => true,
            ],
            
            // Magazzini specializzati
            [
                'name' => 'Area Quarantena Qualità',
                'type' => 'magazzino'
            ],
            [
                'name' => 'Deposito Resi e Scarti',
                'type' => 'magazzino'
            ],
            [
                'name' => 'Magazzino Spedizioni',
                'type' => 'magazzino'
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(
                ['name' => $warehouse['name']],
                $warehouse
            );
        }

        $this->command->info('✅ Creati ' . count($warehouses) . ' magazzini/fornitori/negozi');
    }
}
