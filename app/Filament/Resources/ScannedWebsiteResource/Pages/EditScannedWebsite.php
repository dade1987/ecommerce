<?php

namespace App\Filament\Resources\ScannedWebsiteResource\Pages;

use App\Filament\Resources\ScannedWebsiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditScannedWebsite extends EditRecord
{
    protected static string $resource = ScannedWebsiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
