<?php

namespace App\Filament\Resources\LogisticProductResource\Pages;

use App\Filament\Resources\LogisticProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLogisticProduct extends EditRecord
{
    protected static string $resource = LogisticProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
