<?php

namespace Database\Seeders;

use App\Models\Bom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bom::create([
            'product_name' => 'Pannello Standard Foro Tondo R5T8',
            'internal_code' => 'LAM-ST-R5T8-INOX304',
            'materials' => [
                [
                    'material_type' => 'Lamiera in Acciaio Inox AISI 304',
                    'thickness' => '1.5',
                    'quantity' => '1',
                ],
                [
                    'material_type' => 'Viti in Acciaio Inox',
                    'thickness' => '0.0',
                    'quantity' => '8',
                ],
            ],
        ]);

        Bom::create([
            'product_name' => 'Pannello Decorativo per Facciata Q10',
            'internal_code' => 'PAN-DEC-Q10-ALU-W',
            'materials' => [
                [
                    'material_type' => 'Lamiera in Alluminio Preverniciato Bianco',
                    'thickness' => '2.0',
                    'quantity' => '1',
                ],
                [
                    'material_type' => 'Rivetti in Alluminio',
                    'thickness' => '0.0',
                    'quantity' => '12',
                ],
            ],
        ]);

        Bom::create([
            'product_name' => 'Griglia Aerazione Industriale',
            'internal_code' => 'GRID-IND-L6-FEZN',
            'materials' => [
                [
                    'material_type' => 'Lamiera in Ferro Zincato a Caldo',
                    'thickness' => '3.0',
                    'quantity' => '1',
                ],
                [
                    'material_type' => 'Viti in Acciaio Zincato',
                    'thickness' => '0.0',
                    'quantity' => '10',
                ],
            ],
        ]);
    }
}
