<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quoter extends Model
{
    protected $fillable = ['thread_id', 'role', 'content', 'is_fake'];

    use HasFactory;

    protected $casts = [
        'is_fake' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
