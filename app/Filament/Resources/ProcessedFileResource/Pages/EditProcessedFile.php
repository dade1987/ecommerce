<?php

namespace App\Filament\Resources\ProcessedFileResource\Pages;

use App\Filament\Resources\ProcessedFileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessedFile extends EditRecord
{
    protected static string $resource = ProcessedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
