<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ProductTwin;

class CreateInventoryMovement extends CreateRecord
{
    protected static string $resource = InventoryMovementResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        // Logica per CARICO
        if ($record->movement_type === 'carico' && $record->quantity > 0) {
            $product = $record->internalProduct;
            $twins = [];
            for ($i = 0; $i < $record->quantity; $i++) {
                $twin = ProductTwin::create([
                    'internal_product_id' => $record->internal_product_id,
                    'current_warehouse_id' => $record->to_warehouse_id,
                    'lifecycle_status' => 'in_stock',
                    'co2_emissions_production' => $product->emission_factor ?? 0,
                    'co2_emissions_logistics' => 0,
                    'co2_emissions_total' => $product->emission_factor ?? 0,
                    'metadata' => [
                        'carico_inventory_movement_id' => $record->id,
                        'note' => $record->note,
                    ],
                ]);
                $twins[] = $twin->id;
            }
            $record->productTwins()->attach($twins);
        }

        // Logica per MOVIMENTI di ProductTwin esistenti (Trasferimento, Scarico, Reso)
        if (in_array($record->movement_type, ['trasferimento', 'scarico', 'reso'])) {
            $twinIds = $this->data['product_twins'] ?? [];

            if (!empty($twinIds)) {
                // 1. Aggiorna lo stato di ogni Product Twin
                $twinsToUpdate = ProductTwin::find($twinIds);
                foreach ($twinsToUpdate as $twin) {
                    if ($record->movement_type === 'trasferimento' || $record->movement_type === 'reso') {
                        $twin->current_warehouse_id = $record->to_warehouse_id;
                    }
                    if ($record->movement_type === 'scarico') {
                        $twin->current_warehouse_id = null;
                        $twin->lifecycle_status = 'sold';
                    }
                    $twin->save();
                }

                // 2. Collega i Product Twin a questo movimento
                $record->productTwins()->sync($twinIds);
            }
        }
    }
}
