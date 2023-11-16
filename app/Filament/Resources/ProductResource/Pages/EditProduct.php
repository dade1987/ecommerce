<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;
use SevendaysDigital\FilamentNestedResources\ResourcePages\NestedPage;

class EditProduct extends EditRecord
{
    use NestedPage;
    
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
