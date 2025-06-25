@aware(['page'])

<x-filament::slide-over id="reservation-calendar">
    <x-slot name="heading">
        {{ $title ?? 'Effettua una prenotazione' }}
    </x-slot>

    <div class="p-4">
        <p class="text-sm text-gray-500 mb-4">Clicchi sopra ad un orario per effettuare una prenotazione.</p>

        @livewire(App\Filament\Resources\ReservationResource\Widgets\CalendarWidget::class)
    </div>
</x-filament::slide-over>
