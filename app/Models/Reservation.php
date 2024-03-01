<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{

    use HasFactory;

    protected $fillable = ['name', 'people_number', 'starts_at', 'ends_at', 'telephone_number', 'allergens'];
}
