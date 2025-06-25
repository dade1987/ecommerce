<?php

namespace App\Livewire;

use Livewire\Component;

class OpenCalendarButton extends Component
{
    public string $style;

    public function mount(string $style = 'primary')
    {
        $this->style = $style;
    }

    public function open()
    {
        $this->dispatch('open-calendar');
    }

    public function render()
    {
        return view('livewire.open-calendar-button');
    }
} 