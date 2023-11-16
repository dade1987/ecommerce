<?php

namespace App\Filament\Resources\ProductMorphResource\Pages;

use App\Filament\Resources\ProductMorphResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductMorphs extends ListRecords
{
    protected static string $resource = ProductMorphResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
