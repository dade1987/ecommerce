<?php

namespace Database\Seeders;

use App\Models\LogisticProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogisticProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Materie prime alimentari
            [
                'codice' => 'MP-001',
                'nome' => 'Farina Tipo 00',
                'descrizione' => 'Farina di grano tenero tipo 00 per panificazione e pasticceria',
                'unita_misura' => 'kg'
            ],
            [
                'codice' => 'MP-002',
                'nome' => 'Zucchero Semolato',
                'descrizione' => 'Zucchero bianco semolato per uso alimentare',
                'unita_misura' => 'kg'
            ],
            [
                'codice' => 'MP-003',
                'nome' => 'Olio Extra Vergine',
                'descrizione' => 'Olio extravergine di oliva primo spremitura',
                'unita_misura' => 'litri'
            ],
            [
                'codice' => 'MP-004',
                'nome' => 'Sale Fino',
                'descrizione' => 'Sale marino fino per uso alimentare',
                'unita_misura' => 'kg'
            ],
            
            // Ingredienti specifici
            [
                'codice' => 'ING-001',
                'nome' => 'Lievito di Birra',
                'descrizione' => 'Lievito di birra fresco per panificazione',
                'unita_misura' => 'pz'
            ],
            [
                'codice' => 'ING-002',
                'nome' => 'Vanillina',
                'descrizione' => 'Aroma di vanillina in polvere',
                'unita_misura' => 'grammi'
            ],
            [
                'codice' => 'ING-003',
                'nome' => 'Cacao in Polvere',
                'descrizione' => 'Cacao amaro in polvere per dolci',
                'unita_misura' => 'kg'
            ],
            
            // Packaging e contenitori
            [
                'codice' => 'PKG-001',
                'nome' => 'Sacchetti Carta 500g',
                'descrizione' => 'Sacchetti in carta alimentare da 500 grammi',
                'unita_misura' => 'pz'
            ],
            [
                'codice' => 'PKG-002',
                'nome' => 'Contenitori Plastica 1L',
                'descrizione' => 'Contenitori in plastica alimentare da 1 litro',
                'unita_misura' => 'pz'
            ],
            [
                'codice' => 'PKG-003',
                'nome' => 'Etichette Adesive',
                'descrizione' => 'Etichette adesive per identificazione prodotti',
                'unita_misura' => 'pz'
            ],
            
            // Materiali tecnici
            [
                'codice' => 'TEC-001',
                'nome' => 'Nastro Adesivo',
                'descrizione' => 'Nastro adesivo trasparente per imballaggio',
                'unita_misura' => 'rotoli'
            ],
            [
                'codice' => 'TEC-002',
                'nome' => 'Pellicola Trasparente',
                'descrizione' => 'Pellicola trasparente per alimenti',
                'unita_misura' => 'metri'
            ],
            
            // Prodotti finiti
            [
                'codice' => 'FIN-001',
                'nome' => 'Pane Bianco 400g',
                'descrizione' => 'Pane bianco classico da 400 grammi',
                'unita_misura' => 'pz'
            ],
            [
                'codice' => 'FIN-002',
                'nome' => 'Biscotti Frollini',
                'descrizione' => 'Biscotti frollini confezionati da 250g',
                'unita_misura' => 'confezioni'
            ],
            [
                'codice' => 'FIN-003',
                'nome' => 'Torta Cioccolato',
                'descrizione' => 'Torta al cioccolato pronta da 500g',
                'unita_misura' => 'pz'
            ],
            
            // Materiali di pulizia
            [
                'codice' => 'PUL-001',
                'nome' => 'Detergente Sgrassante',
                'descrizione' => 'Detergente sgrassante per superfici alimentari',
                'unita_misura' => 'litri'
            ],
            [
                'codice' => 'PUL-002',
                'nome' => 'Sanificante Mani',
                'descrizione' => 'Gel sanificante per le mani',
                'unita_misura' => 'litri'
            ],
        ];

        foreach ($products as $product) {
            LogisticProduct::updateOrCreate(
                ['codice' => $product['codice']],
                $product
            );
        }

        $this->command->info('✅ Creati ' . count($products) . ' prodotti logistici');
    }
}
