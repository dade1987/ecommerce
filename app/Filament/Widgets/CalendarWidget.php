<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\EventResource;
use Filament\Forms\Components\DateTimePicker;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Event::class;
    public function fetchEvents(array $fetchInfo): array
    {
        return Event::query()
            //->where('starts_at', '>=', $fetchInfo['start'])
            //->where('ends_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(
                fn (Event $event) => EventData::make()
                    ->id($event->id)
                    ->title($event->name)
                    ->start($event->starts_at)
                    ->end($event->ends_at)
                    /*->url(
                        url: EventResource::getUrl(name: 'view', parameters: ['record' => $event]),
                        shouldOpenUrlInNewTab: true
                    )*/
            )
            ->all();
    }

    public function getFormSchema(): array
    {
        return [
            TextInput::make('name'),

            Grid::make()
                ->schema([
                    DateTimePicker::make('starts_at'),

                    DateTimePicker::make('ends_at'),
                ]),
        ];
    }
}
