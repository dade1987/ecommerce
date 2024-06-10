<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quoter extends Model
{
    protected $fillable = ['thread_id', 'role', 'content'];

    use HasFactory;
}
