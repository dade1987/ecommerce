<?php

namespace App\Filament\Resources\WebsiteSearchResource\Pages;

use App\Filament\Resources\WebsiteSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebsiteSearches extends ListRecords
{
    protected static string $resource = WebsiteSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Le ricerche vengono create automaticamente dal sistema
        ];
    }
}
