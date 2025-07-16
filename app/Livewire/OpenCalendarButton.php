<?php

namespace App\Livewire;

use Livewire\Component;

class OpenCalendarButton extends Component
{
    public string $style;
    public string $text;

    public function mount(string $style = 'primary', string $text = _('frontend.book_call'))
    {
        $this->style = rtrim(trim($style), ';');
        $this->text = $text;
    }

    public function open()
    {
        $this->dispatch('open-calendar-slideover');
    }

    public function render()
    {
        return view('livewire.open-calendar-button');
    }
} 