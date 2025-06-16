<?php

namespace App\Filament\Resources\ExtractorResource\Pages;

use App\Filament\Resources\ExtractorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExtractors extends ListRecords
{
    protected static string $resource = ExtractorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
