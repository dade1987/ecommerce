<?php

namespace App\Livewire;

use App\Models\Quoter;
use Livewire\Component;

class Quote extends Component
{
    public $quotes;

    public function mount()
    {
        if (auth()->check()) {
            $this->quotes = Quoter::get();
        } else {
            abort(403);
        }
    }

    public function render()
    {
        return view('livewire.quote');
    }
}
