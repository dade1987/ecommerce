<?php

namespace App\Filament\Resources\IncomingEmailResource\Pages;

use App\Filament\Resources\IncomingEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIncomingEmails extends ListRecords
{
    protected static string $resource = IncomingEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
