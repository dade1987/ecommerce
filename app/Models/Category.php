<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\Traits\HasTeams;

class Category extends Model
{
    use HasFactory;


    use HasTeams;
    protected $fillable = ['name'];



    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id', 'id');
    }

    public function products(): MorphToMany
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

    public function getActionAttribute()
    {
        return ' onClick=location.href=\'' . route('{item2?}.index', ['container0' => 'categories', 'item0' => $this, 'container1' => 'products']) . '\' ';
    }

    public function getActionTextAttribute()
    {
        return 'Apri Categoria';
    }
}
