<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use VanOns\Laraberg\Traits\RendersContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GuttenbergPage extends Model
{
    use HasFactory;

    use RendersContent;

    protected $fillable = ['content'];
}
