<?php

namespace App\Filament\Resources\TableAliasResource\Pages;

use App\Filament\Resources\TableAliasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTableAliases extends ListRecords
{
    protected static string $resource = TableAliasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
