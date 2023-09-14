<?php

namespace App\Models;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductMorph;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'featured_image_id'];

    //relations
    public function orders(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphedByMany(Order::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }


    public function variations(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphToMany(Product::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }


    //inversa delle varianti
    public function products(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphedByMany(Product::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id', 'id');
    }

    //mutators

    public function getActionTextAttribute()
    {
        return 'Aggiungi al Carrello';
    }

    public function getActionAttribute()
    {
        return ' wire:click="$dispatch(\'add-to-cart\', { product_id: \'' . $this->id . '\' })"';
    }
}
