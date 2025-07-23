<?php

namespace App\Filament\Resources\WarehouseResource\Pages;

use App\Filament\Resources\WarehouseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder|null
    {
        // Conta i product twins spostati in entrata e uscita
        return parent::getTableQuery()
            ->withCount([
                'incomingMovements as incomingMovements_count' => function ($query) {
                    $query->join('inventory_movement_product_twin as impt', 'impt.inventory_movement_id', '=', 'logistic_inventory_movements.id')
                        ->selectRaw('count(impt.product_twin_id)')
                        ->groupBy('logistic_inventory_movements.to_warehouse_id');
                },
                'outgoingMovements as outgoingMovements_count' => function ($query) {
                    $query->join('inventory_movement_product_twin as impt', 'impt.inventory_movement_id', '=', 'logistic_inventory_movements.id')
                        ->selectRaw('count(impt.product_twin_id)')
                        ->groupBy('logistic_inventory_movements.from_warehouse_id');
                },
                // Conta i ProductTwin attualmente presenti in magazzino
                'currentTwins as twins_count' => function ($query) {
                    $query->selectRaw('count(*)');
                }
            ]);
    }
}
