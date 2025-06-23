<?php

namespace App\Models;

use App\Models\Traits\HasTeams;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Z3d0X\FilamentFabricator\Models\Page as ModelsPage;

class Page extends ModelsPage
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'layout',
        'parent_id',
        'blocks',
    ];

    use HasTeams;
}
