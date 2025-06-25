<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class CalendarSlideover extends Component
{
    #[On('open-calendar-slideover')]
    public function open()
    {
        Log::info('open-calendar-slideover event received. Dispatching open-modal.');
        $this->dispatch('open-modal', id: 'reservation-calendar');
        $this->dispatch('calendar-opened');
    }

    public function render()
    {
        return view('livewire.calendar-slideover');
    }
}
