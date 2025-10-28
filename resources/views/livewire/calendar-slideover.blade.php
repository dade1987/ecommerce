<div
    x-data="{}"
    x-on:calendar-opened.window="
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 300)
    "
>
    <div class="p-4">
        <h2 class="text-lg font-bold mb-4">Effettua una prenotazione</h2>
        <p class="text-sm text-gray-500 mb-4">Clicchi sopra ad un orario per effettuare una prenotazione.</p>
        <div class="min-h-[80vh]">
            @livewire(App\Filament\Resources\ReservationResource\Widgets\CalendarWidget::class)
        </div>
    </div>
</div>
