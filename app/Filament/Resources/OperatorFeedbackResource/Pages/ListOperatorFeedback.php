<?php

namespace App\Filament\Resources\OperatorFeedbackResource\Pages;

use App\Filament\Resources\OperatorFeedbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperatorFeedback extends ListRecords
{
    protected static string $resource = OperatorFeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
