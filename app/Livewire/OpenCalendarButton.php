<?php

namespace App\Livewire;

use Livewire\Component;

class OpenCalendarButton extends Component
{
    public function trigger()
    {
        $this->dispatch('open-calendar-slideover');
    }

    public function render()
    {
        return view('livewire.open-calendar-button');
    }
} 