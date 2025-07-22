<?php

namespace Database\Seeders;

use App\Models\Bom;
use App\Models\InternalProduct;
use App\Models\LogisticProduct;
use App\Models\ProductionOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateToInternalProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->command->info('Migrating Logistic Products to Internal Products...');
            
            $logisticProducts = LogisticProduct::all();
            
            foreach ($logisticProducts as $logisticProduct) {
                $internalProduct = InternalProduct::updateOrCreate(
                    ['code' => $logisticProduct->codice],
                    [
                        'name' => $logisticProduct->nome,
                        'description' => $logisticProduct->descrizione,
                        'unit_of_measure' => $logisticProduct->unita_misura,
                    ]
                );

                // Update inventory movements
                $logisticProduct->inventoryMovements()->update(['internal_product_id' => $internalProduct->id]);
            }

            $this->command->info('Updating Boms with Internal Product ID...');
            $boms = Bom::whereNull('internal_product_id')->get();
            foreach ($boms as $bom) {
                $internalProduct = InternalProduct::where('code', $bom->internal_code)->first();
                if ($internalProduct) {
                    $bom->update(['internal_product_id' => $internalProduct->id]);
                }
            }

            $this->command->info('Updating Production Orders with Internal Product ID...');
            $orders = ProductionOrder::whereNull('internal_product_id')->with('bom')->get();
            foreach ($orders as $order) {
                if ($order->bom && $order->bom->internal_product_id) {
                    $order->update(['internal_product_id' => $order->bom->internal_product_id]);
                }
            }
        });
    }
}
