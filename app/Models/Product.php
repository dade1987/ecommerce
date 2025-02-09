<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Order;
use App\Models\ProductMorph;
use App\Models\Traits\HasTeams;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Route;
use Spatie\EloquentSortable\SortableTrait;

class Product extends Model
{
    use HasFactory;
    use HasTeams;
    use SortableTrait;

    protected $fillable = ['team_id', 'name', 'description', 'price', 'featured_image_id', 'order_column', 'weight', 'slug', 'emission_factor'];

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];

    // come identifico il padre da cui ho chiamato il figlio in una relazione morphTo, usando la relazione?
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

        return $this->morphToMany(self::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
        // ->wherePivot('type', 'component');
    }

    public function allergens(): MorphToMany
    {
        return $this->subproducts()->wherePivot('type', 'allergen');
    }

    public function ingredients(): MorphToMany
    {
        return $this->subproducts()->wherePivot('type', 'ingredient');
    }

    public function variations(): MorphToMany
    {
        return $this->subproducts()->wherePivot('type', 'variation');
    }

    public function productMorph()
    {
        return $this->hasMany(ProductMorph::class, 'product_id', 'id')->where('model_type', Category::class);
    }

    public function categories(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();

        return $this->morphedByMany(Category::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }

    public function scopeOfCategory($query, $parent)
    {
        return $query->whereHas('categories', function ($query) use ($parent) {
            $query->where('categories.id', $parent);
        });
    }

    public function category($query, $parent)
    {
        return $query->whereHas('categories', function ($query) use ($parent) {
            $query->where('categories.id', $parent);
        });
    }

    //inversa delle varianti
    public function products(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();

        return $this->morphedByMany(self::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }

    public function scopeOfProduct($query, $parent)
    {
        return $query->whereHas('products', function ($query) use ($parent) {
            $query->where('product.id', $parent);
        });
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id', 'id');
    }

    // ------------------------ mutators -------------------------------

    public function getActionTextAttribute()
    {
        return 'Aggiungi al Carrello';
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

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
