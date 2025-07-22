<?php

namespace App\Observers;

use App\Models\InventoryMovement;
use App\Models\ProductTwin;

class InventoryMovementObserver
{
    /**
     * Handle the InventoryMovement "created" event.
     */
    public function created(InventoryMovement $inventoryMovement): void
    {
        // Emission factors are now read from the config file
        $emissionFactors = config('carbon.transport_modes', []);

        $co2Logistic = 0;
        if ($inventoryMovement->distance_km && $inventoryMovement->transport_mode) {
            $factor = $emissionFactors[$inventoryMovement->transport_mode] ?? 0;
            $co2Logistic = $inventoryMovement->distance_km * $factor;
        }

        // Handle 'load' movements: create new twins
        if ($inventoryMovement->movement_type === 'load') {
            if ($inventoryMovement->quantity > 0 && $inventoryMovement->internal_product_id) {
                $newTwins = [];
                for ($i = 0; $i < $inventoryMovement->quantity; $i++) {
                    $twin = ProductTwin::create([
                        'internal_product_id' => $inventoryMovement->internal_product_id,
                        'current_warehouse_id' => $inventoryMovement->to_warehouse_id,
                        'lifecycle_status' => 'in_stock',
                        'co2_emissions_logistics' => $co2Logistic, // Assign logistic CO2 of this first movement
                        'co2_emissions_total' => $co2Logistic,
                    ]);
                    $newTwins[] = $twin->id;
                }
                // Attach the new twins to the movement record
                $inventoryMovement->productTwins()->attach($newTwins);
            }
        }
        // Handle 'unload' and 'transfer' movements: update existing twins
        else if (in_array($inventoryMovement->movement_type, ['unload', 'transfer'])) {
            $productTwins = $inventoryMovement->productTwins;
            $newStatus = 'in_use'; // Default for unload

            if ($inventoryMovement->movement_type === 'transfer') {
                if ($inventoryMovement->toWarehouse && $inventoryMovement->toWarehouse->is_final_destination) {
                    $newStatus = 'in_transit';
                } else {
                    $newStatus = 'in_stock'; // It's just moving to another internal warehouse
                }
            }

            foreach ($productTwins as $twin) {
                $twin->increment('co2_emissions_logistics', $co2Logistic);
                $twin->increment('co2_emissions_total', $co2Logistic);
                $twin->update([
                    'lifecycle_status' => $newStatus,
                    'current_warehouse_id' => $inventoryMovement->to_warehouse_id, // This will be null on unload
                ]);
            }
        }
    }

    /**
     * Handle the InventoryMovement "updated" event.
     */
    public function updated(InventoryMovement $inventoryMovement): void
    {
        //
    }

    /**
     * Handle the InventoryMovement "deleted" event.
     */
    public function deleted(InventoryMovement $inventoryMovement): void
    {
        //
    }

    /**
     * Handle the InventoryMovement "restored" event.
     */
    public function restored(InventoryMovement $inventoryMovement): void
    {
        //
    }

    /**
     * Handle the InventoryMovement "force deleted" event.
     */
    public function forceDeleted(InventoryMovement $inventoryMovement): void
    {
        //
    }
}
