<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class CalendarSlideover extends Component
{
    #[On('open-calendar-slideover')]
    public function open()
    {
        $this->dispatch('open-modal', id: 'reservation-calendar');
    }

    public function render()
    {
        return view('livewire.calendar-slideover');
    }
}
