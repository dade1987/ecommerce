<?php

namespace App\Filament\Resources\ScannedWebsiteResource\Pages;

use App\Filament\Resources\ScannedWebsiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScannedWebsites extends ListRecords
{
    protected static string $resource = ScannedWebsiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
