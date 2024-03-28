@aware(['page'])
<a name="reservation-calendar"></a>
<div class="px-4 py-4 md:py-8">
    <div class="max-w-6xl mx-auto">
        {{--@livewire(App\Filament\Widgets\CalendarWidget::class)--}}
        <div class="grid md:grid-cols-1 gap-16 items-center relative overflow-hidden p-10 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.3)] rounded-3xl max-w-6xl mx-auto bg-white text-[#333] my-6 font-[sans-serif] before:absolute before:right-0 before:w-[300px] before:h-full max-md:before:hidden">
            <div>
                <h2 class="text-3xl font-extrabold">Effettua una Prenotazione</h2>
                <p class="text-sm text-gray-400 mt-3">Clicchi sopra ad un orario per effettuare una prenotazione</p>
                
                @livewire(App\Filament\Resources\ReservationResource\Widgets\CalendarWidget::class)
          
            </div>
            
           
        </div>
        
    </div>
</div>
