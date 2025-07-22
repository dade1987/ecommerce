<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'unit_of_measure',
        'weight',
        'materials',
        'emission_factor',
        'co2_avoided',
        'expected_lifespan_days',
        'is_recyclable',
        'metadata',
    ];

    protected $casts = [
        'materials' => 'array',
        'metadata' => 'array',
        'is_recyclable' => 'boolean',
    ];
}
