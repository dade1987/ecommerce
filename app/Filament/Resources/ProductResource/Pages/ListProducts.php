<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ProductResource;
use SevendaysDigital\FilamentNestedResources\ResourcePages\NestedPage;

class ListProducts extends ListRecords
{
    use NestedPage;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
