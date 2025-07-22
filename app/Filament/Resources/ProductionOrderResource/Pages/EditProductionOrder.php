<?php

namespace App\Filament\Resources\ProductionOrderResource\Pages;

use App\Filament\Resources\ProductionOrderResource;
use App\Models\ProductTwin;
use App\Models\Warehouse;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionOrder extends EditRecord
{
    protected static string $resource = ProductionOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $order = $this->record;

        if ($order->status->value === 'completed' && $order->internal_product_id) {
            
            $totalEnergyConsumption = $order->phases()->sum('energy_consumption');
            $emissionFactor = $order->internalProduct->emission_factor ?? 0;
            $totalCo2 = $totalEnergyConsumption * $emissionFactor;
            $co2PerUnit = $order->quantity > 0 ? $totalCo2 / $order->quantity : 0;
            
            $finishedGoodsWarehouseId = config('production.default_finished_goods_warehouse_id');

            for ($i = 0; $i < $order->quantity; $i++) {
                ProductTwin::create([
                    'internal_product_id' => $order->internal_product_id,
                    'current_warehouse_id' => $finishedGoodsWarehouseId,
                    'lifecycle_status' => 'in_stock',
                    'co2_emissions_production' => $co2PerUnit,
                    'co2_emissions_total' => $co2PerUnit, // At this point, total is just production
                ]);
            }
        }
    }
}
