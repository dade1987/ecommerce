<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductTwin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductTwinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            ProductTwin::create([
                'product_id' => $product->id,
            ]);
        }
    }
}
