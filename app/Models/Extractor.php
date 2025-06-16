<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'prompt',
        'export_format',
        'export_class',
    ];
}
