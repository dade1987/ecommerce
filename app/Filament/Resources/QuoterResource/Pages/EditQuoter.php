<?php

namespace App\Filament\Resources\QuoterResource\Pages;

use App\Filament\Resources\QuoterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuoter extends EditRecord
{
    protected static string $resource = QuoterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
