<?php

namespace App\Livewire;

use Digikraaft\ReviewRating\Models\Review;
use Livewire\Component;

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
