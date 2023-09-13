<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'featured_image_id'];

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id', 'id');
    }

    public function getActionTextAttribute()
    {
        return 'Aggiungi al Carrello';
    }

    public function getActionAttribute()
    {
        return ' wire:click="$dispatch(\'add-to-cart\', { product_id: \'' . $this->id . '\' })"';
    }
}
