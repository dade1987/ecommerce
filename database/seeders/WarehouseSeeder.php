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
                'nome' => 'Molino San Giuseppe',
                'tipo' => 'fornitore'
            ],
            [
                'nome' => 'Oleificio Toscano',
                'tipo' => 'fornitore'
            ],
            [
                'nome' => 'Zuccherificio Nazionale',
                'tipo' => 'fornitore'
            ],
            [
                'nome' => 'Caseificio Valle Verde',
                'tipo' => 'fornitore'
            ],
            [
                'nome' => 'Fornitore Packaging SRL',
                'tipo' => 'fornitore'
            ],
            [
                'nome' => 'Ingredienti & Co.',
                'tipo' => 'fornitore'
            ],
            
            // Magazzini centrali
            [
                'nome' => 'Magazzino Centrale Nord',
                'tipo' => 'magazzino'
            ],
            [
                'nome' => 'Magazzino Centrale Sud',
                'tipo' => 'magazzino'
            ],
            [
                'nome' => 'Deposito Materie Prime',
                'tipo' => 'magazzino'
            ],
            [
                'nome' => 'Deposito Prodotti Finiti',
                'tipo' => 'magazzino'
            ],
            [
                'nome' => 'Magazzino Refrigerato',
                'tipo' => 'magazzino'
            ],
            
            // Negozi e punti vendita
            [
                'nome' => 'Negozio Centro Storico',
                'tipo' => 'negozio'
            ],
            [
                'nome' => 'Punto Vendita Periferia',
                'tipo' => 'negozio'
            ],
            [
                'nome' => 'Outlet Factory Store',
                'tipo' => 'negozio'
            ],
            [
                'nome' => 'Corner Supermercato',
                'tipo' => 'negozio'
            ],
            [
                'nome' => 'Negozio Online (E-commerce)',
                'tipo' => 'negozio'
            ],
            
            // Magazzini specializzati
            [
                'nome' => 'Area Quarantena Qualità',
                'tipo' => 'magazzino'
            ],
            [
                'nome' => 'Deposito Resi e Scarti',
                'tipo' => 'magazzino'
            ],
            [
                'nome' => 'Magazzino Spedizioni',
                'tipo' => 'magazzino'
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(
                ['nome' => $warehouse['nome']],
                $warehouse
            );
        }

        $this->command->info('✅ Creati ' . count($warehouses) . ' magazzini/fornitori/negozi');
    }
}
