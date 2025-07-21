<?php

namespace App\Filament\Resources\WorkstationResource\Pages;

use App\Filament\Resources\WorkstationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkstation extends EditRecord
{
    protected static string $resource = WorkstationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
