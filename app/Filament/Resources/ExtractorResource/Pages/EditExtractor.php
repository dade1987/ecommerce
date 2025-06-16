<?php

namespace App\Filament\Resources\ExtractorResource\Pages;

use App\Filament\Resources\ExtractorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExtractor extends EditRecord
{
    protected static string $resource = ExtractorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
