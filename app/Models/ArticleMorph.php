<?php

namespace App\Models;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class ArticleMorph extends MorphPivot
{
    protected $fillable = [];

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'model_id')->where('model_type', Tag::class);
    }
}
