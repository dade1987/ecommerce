<?php

namespace App\Filament\Resources\ProductionLineResource\Pages;

use App\Filament\Resources\ProductionLineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductionLine extends EditRecord
{
    protected static string $resource = ProductionLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
