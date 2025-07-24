<?php

namespace App\Filament\Resources\FidelityCardResource\Pages;

use App\Filament\Resources\FidelityCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFidelityCards extends ListRecords
{
    protected static string $resource = FidelityCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
