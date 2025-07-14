<?php

namespace App\Filament\Resources\IncomingEmailResource\Pages;

use App\Filament\Resources\IncomingEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIncomingEmail extends EditRecord
{
    protected static string $resource = IncomingEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
