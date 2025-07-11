<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'internal_code',
        'materials',
    ];

    protected $casts = [
        'materials' => 'array',
    ];
}
