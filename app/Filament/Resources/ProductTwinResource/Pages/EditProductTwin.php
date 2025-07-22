<?php

namespace App\Filament\Resources\ProductTwinResource\Pages;

use App\Filament\Resources\ProductTwinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductTwin extends EditRecord
{
    protected static string $resource = ProductTwinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
