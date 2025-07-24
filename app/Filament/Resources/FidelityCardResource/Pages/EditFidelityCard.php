<?php

namespace App\Filament\Resources\FidelityCardResource\Pages;

use App\Filament\Resources\FidelityCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFidelityCard extends EditRecord
{
    protected static string $resource = FidelityCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
