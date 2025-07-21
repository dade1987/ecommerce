<?php

namespace App\Filament\Resources\ProductionLineResource\Pages;

use App\Filament\Resources\ProductionLineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionLines extends ListRecords
{
    protected static string $resource = ProductionLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
