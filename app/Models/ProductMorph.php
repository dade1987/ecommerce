<?php

namespace App\Models;

use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class ProductMorph extends MorphPivot
{
    protected $fillable = ['type'];


    public function category()
    {
        return $this->belongsTo(Category::class, 'model_id')->where('model_type', Category::class);
    }

}
