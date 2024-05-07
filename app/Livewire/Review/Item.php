<?php

namespace App\Livewire\Review;

use App\Models\Review;
use Livewire\Component;

class Item extends Component
{
    public $review;
    public function mount(Review $review)
    {
        $this->review = $review;
    }
    public function render()
    {
        return view('livewire.review.item');
    }
}
