<?php

namespace App\Filament\Resources\ProductTwinResource\Pages;

use App\Filament\Resources\ProductTwinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductTwins extends ListRecords
{
    protected static string $resource = ProductTwinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
