<?php

namespace App\Filament\Resources\ReservationResource\Widgets;

use Carbon\Carbon;
use Filament\Forms\Form;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\ReservationResource;


class CalendarWidget extends FullCalendarWidget
{

    public function getConfig(): array
    {
        return [
            'initialView' => 'timeGridWeek',
            'headerToolbar' => [
                'left' => 'prev,next',
                'center' => 'title',
                'right' => '',
            ],
            'views' => [
                'timeGridWeek' => [
                    'dayHeaderFormat' => [
                        'weekday' => 'short',
                        'month' => 'numeric',
                        'day' => 'numeric',
                        'omitCommas' => true,
                    ],
                ],
            ],
            'height' => 'auto',
            'windowResize' => "
                function(arg) {
                    if (arg.view.type === 'timeGridWeek' && window.innerWidth < 640) {
                        this.changeView('timeGridDay');
                    }
                }
            ",
        ];
    }

    public Model|string|null $model = Reservation::class;

    public bool $selectable = true;

    protected function headerActions(): array
    {
        //tutti possono creare eventi
        return [];
    }


    protected function modalActions(): array
    {
        return [
            CreateAction::make()
                ->mountUsing(
                    function (Form $form, array $arguments) {

                        $start = $arguments['start'] ?? null;
                        $end = $arguments['end'] ?? null;

                        if ($start && is_null($end)) {
                            $end = Carbon::parse($start)->addHour();
                        }

                        $form->fill([
                            'starts_at' => $start,
                            'ends_at' => $end
                        ]);
                    }
                )
                ->after(function () {
                    $this->dispatch('gtag_report_conversion');
                })
        ];
    }


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
            ->where('starts_at', '>=', $fetchInfo['start'])
            ->where('ends_at', '<=', $fetchInfo['end'])

            ->get()
            ->map(
                fn (Reservation $event) => [
                    'id' => $event->id,
                    'title' => 'Slot Occupato',
                    'start' => $event->starts_at,
                    'end' => $event->ends_at,
                    'backgroundColor' => 'red',
                    'borderColor' => 'red',
                    'allDay' => false
                ]
            )
            ->all();
    }
    public function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required(),
            TextInput::make('telephone_number')
                ->label('Telephone Number')
                ->required(),
            Textarea::make('request_notes')
                ->label('La tua richiesta (opzionale)'),
            DateTimePicker::make('starts_at')->readOnly(),
            DateTimePicker::make('ends_at')->readOnly(),
        ];
    }

    public function onEventClick(array $event): void
    {
        $reservation = Reservation::find($event['id']);

        if (!$reservation) {
            return;
        }

        $user = Auth::user();

        if (!$user || $user->id !== $reservation->user_id) {
            // Prevent opening the event if user is not logged in or not the owner
            return;
        }

        // If authorized, open the event for editing.
        // This will redirect to the edit page of the ReservationResource.
        $this->redirect(ReservationResource::getUrl('edit', ['record' => $reservation->id]));
    }

    public static function canView(): bool
    {
        return true;
    }
}
