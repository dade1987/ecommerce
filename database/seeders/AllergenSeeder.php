<?php

namespace Database\Seeders;

use App\Models\Allergen;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AllergenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Allergen::create(['name' => 'glutine']);
        Allergen::create(['name' => 'cereali che contengono glutine']);
        Allergen::create(['name' => 'latticini']);
        Allergen::create(['name' => 'uova']);
        Allergen::create(['name' => 'latticini e derivati del latte']);
        Allergen::create(['name' => 'crostacei']);
        Allergen::create(['name' => 'molluschi']);
        Allergen::create(['name' => 'molluschi cefalopodi']);
        Allergen::create(['name' => 'pesce']);
        Allergen::create(['name' => 'soia']);
        Allergen::create(['name' => 'legumi']);
        Allergen::create(['name' => 'sedano']);
        Allergen::create(['name' => 'frutta in guscio']);
        Allergen::create(['name' => 'arachidi']);
        Allergen::create(['name' => 'senape']);
        Allergen::create(['name' => 'semi di sesamo']);
        Allergen::create(['name' => 'solfiti e solfiti']);
        Allergen::create(['name' => 'lupini']);
        Allergen::create(['name' => 'carne']);
        Allergen::create(['name' => 'maiale']);
        Allergen::create(['name' => 'bovino']);
        Allergen::create(['name' => 'derivati animali']);
        Allergen::create(['name' => 'alimenti halal']);
        Allergen::create(['name' => 'alimenti kosher']);
    }
}
