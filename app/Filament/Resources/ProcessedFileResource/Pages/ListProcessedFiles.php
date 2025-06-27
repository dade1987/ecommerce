<?php

namespace App\Filament\Resources\ProcessedFileResource\Pages;

use App\Filament\Resources\ProcessedFileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcessedFiles extends ListRecords
{
    protected static string $resource = ProcessedFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
