<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['nation', 'region', 'province', 'municipality', 'street', 'postal_code', 'team_id'];

    public function getFullAddressAttribute()
    {
        return $this->street.' - '.$this->municipality.' ('.$this->province.')';
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
