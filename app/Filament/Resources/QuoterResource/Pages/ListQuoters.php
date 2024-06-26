<?php

namespace App\Filament\Resources\QuoterResource\Pages;

use App\Filament\Resources\QuoterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQuoters extends ListRecords
{
    protected static string $resource = QuoterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
