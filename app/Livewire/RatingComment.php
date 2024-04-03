<?php

namespace App\Livewire;

use Livewire\Component;
use Digikraaft\ReviewRating\Models\Review;

class RatingComment extends Component
{
    public Review $review;
    public function mount(int $review_id)
    {
        
        $this->review = Review::find($review_id);
    }

    public function render()
    {
        return view('livewire.rating-comment');
    }
}
