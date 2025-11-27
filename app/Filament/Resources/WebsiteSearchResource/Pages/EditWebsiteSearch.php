<?php

namespace App\Filament\Resources\WebsiteSearchResource\Pages;

use App\Filament\Resources\WebsiteSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWebsiteSearch extends EditRecord
{
    protected static string $resource = WebsiteSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
