<?php

namespace App\Filament\Resources\WorkstationResource\Pages;

use App\Filament\Resources\WorkstationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkstations extends ListRecords
{
    protected static string $resource = WorkstationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
