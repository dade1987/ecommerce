<?php

namespace App\Filament\Resources\ReservationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\ReservationResource;
use App\Filament\Resources\ReservationResource\Widgets\CalendarWidget;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getFooterWidgets(): array
    {
        return [
            CalendarWidget::class
        ];
    }
}
