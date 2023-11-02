<?php

namespace App\Filament\Resources\ReservationResource\Widgets;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;


class CalendarWidget extends FullCalendarWidget
{

    public Model | string | null $model = Reservation::class;
    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        // You can use $fetchInfo to filter events by date.
        // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
        // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
        return Reservation::query()
            //->where('starts_at', '>=', $fetchInfo['start'])
            //->where('ends_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Reservation $event) => [
                    'title' => $event->name,
                    'start' => $event->date_time,
                    // 'end' => $event->ends_at,
                    //'url' => EventResource::getUrl(name: 'view', parameters: ['record' => $event]),
                    //'shouldOpenUrlInNewTab' => true
                ]
            )
            ->all();
    }
    public function getFormSchema(): array
    {
        return [
            TextInput::make('name'),

            // Forms\Components\Grid::make()
            //->schema([
            DateTimePicker::make('date_time'),

            // Forms\Components\DateTimePicker::make('ends_at'),
            // ]),
        ];
    }
}
