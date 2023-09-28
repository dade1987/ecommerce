<?php

namespace App\Models;

use App\Models\Order;
use App\Models\ProductMorph;
use App\Models\Traits\HasTeams;
use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\SortableTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory;

    use HasTeams;
    use SortableTrait;

    protected $fillable = ['name', 'description', 'price', 'featured_image_id', 'order_column', 'weight'];

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

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

    public function subproducts(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphToMany(Product::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
           // ->wherePivot('type', 'component');
    }

    public function variations(): MorphToMany
    {
        return $this->subproducts()->wherePivot('type', 'variation');
    }


    /*public function variations(): MorphToMany
    {

        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphToMany(Product::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
            //->wherePivot('type', 'variation');
    }*/


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
        return ' wire:click="$dispatch(\'add-food-to-cart\', { product_id: \'' . $this->id . '\' })"';
    }

    public function getSecondActionTextAttribute()
    {
        return 'Aggiunte';
    }

    public function getSecondActionAttribute()
    {

        $item0 = Route::current()->parameter('item0');
        return ' onClick=location.href=\'' . route('{item2?}.index', ['container0' => 'categories', 'item0' =>  $item0, 'container1' => 'products', 'item1' => $this, 'container2' => 'variations']) . '\' ';
    }
}
