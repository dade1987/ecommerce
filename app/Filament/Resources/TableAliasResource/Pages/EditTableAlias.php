<?php

namespace App\Filament\Resources\TableAliasResource\Pages;

use App\Filament\Resources\TableAliasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTableAlias extends EditRecord
{
    protected static string $resource = TableAliasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
