<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScannedWebsite extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain',
        'email',
        'phone_number',
        'risk_percentage',
        'critical_points',
        'raw_data',
        'ip_address',
        'scanned_at',
    ];

    protected $casts = [
        'critical_points' => 'array',
        'raw_data' => 'array',
        'scanned_at' => 'datetime',
    ];
}
