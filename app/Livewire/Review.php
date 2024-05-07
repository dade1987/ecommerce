<?php

namespace App\Livewire;

use App\Models\Review as Rev;
use Livewire\Component;

class Review extends Component
{
    public $reviews;

    public function mount()
    {
        $this->reviews=Rev::get();
    }
    public function render()
    {
        return view('livewire.review');
    }
}
