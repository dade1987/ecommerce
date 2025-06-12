<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Digikraaft\ReviewRating\Traits\HasReviewRating;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Restaurant extends Model
{
    use HasReviewRating;
    use HasFactory;

    protected $fillable = ['name', 'price_range', 'phone_number', 'email', 'website', 'featured_image_id'];


    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id', 'id');
    }

    public function addresses(): MorphToMany
    {
        return $this->morphToMany(Address::class, 'model', 'address_morph');
    }

    public function getActionTextAttribute()
    {
        return 'Prenota Tavolo';
    }

    public function getActionAttribute()
    {
        return ' wire:click="$dispatch(\'add-item-to-cart\', { product_id: \''.$this->id.'\' })"';
    }

    public function getSecondActionTextAttribute()
    {
        return 'Vedi Ingredienti';
    }

    public function getSecondActionAttribute()
    {

        $item0 = Route::current()->parameter('item0');

        return ' wire:click="$dispatch(\'ingredients-list\', { product_id: \''.$this->id.'\' })"';
    }

}


