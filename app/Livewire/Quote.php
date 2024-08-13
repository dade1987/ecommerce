<?php

namespace App\Livewire;

use App\Models\Quoter;
use Livewire\Component;

class Quote extends Component
{
    public $quotes;

    public function mount()
    {
        $this->quotes = Quoter::get();
    }

    public function render()
    {
        return view('livewire.quote');
    }
}
