<x-filament::modal id="reservation-calendar" slide-over width="screen">
    <x-slot name="heading">
        Effettua una prenotazione
    </x-slot>

    <div class="p-4">
        <p class="text-sm text-gray-500 mb-4">Clicchi sopra ad un orario per effettuare una prenotazione.</p>
        <div class="min-h-[80vh]">
            @livewire(App\Filament\Resources\ReservationResource\Widgets\CalendarWidget::class)
        </div>
    </div>
</x-filament::modal>
