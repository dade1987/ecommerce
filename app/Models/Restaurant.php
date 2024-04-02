<?php

namespace App\Models;

use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasReviewRating;
    use HasFactory;

    protected $fillable = ['name', 'price_range', 'phone_number', 'email', 'website'];
}
