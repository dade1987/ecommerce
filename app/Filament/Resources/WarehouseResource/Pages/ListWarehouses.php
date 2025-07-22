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
        return parent::getTableQuery()
            ->withSum([
                'incomingMovements as incomingMovements_count' => function ($query) {
                    $query->selectRaw('coalesce(sum(quantity),0)');
                },
                'outgoingMovements as outgoingMovements_count' => function ($query) {
                    $query->selectRaw('coalesce(sum(quantity),0)');
                },
            ], 'quantity')
            ->withCount([
                // Conta i ProductTwin attualmente presenti in magazzino
                'currentTwins as twins_count' => function ($query) {
                    $query->selectRaw('count(*)');
                }
            ]);
    }
}
