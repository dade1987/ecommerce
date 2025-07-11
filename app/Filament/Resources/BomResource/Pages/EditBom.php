<?php

namespace App\Filament\Resources\BomResource\Pages;

use App\Filament\Resources\BomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBom extends EditRecord
{
    protected static string $resource = BomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
