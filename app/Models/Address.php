<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['nation', 'region', 'province', 'municipality', 'street', 'postal_code', 'team_id', 'latitude', 'longitude'];

    protected $appends = [
        'location',
    ];

    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'latitude',
            'lng' => 'longitude',
        ];
    }

    public function getLocationAttribute(): array
    {
        return [
            'lat' => $this->latitude ? floatval($this->latitude) : 0,
            'lng' => $this->longitude ? floatval($this->longitude) : 0,
        ];
    }

    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location)) {
            $this->latitude = $location['lat'] ?? null;
            $this->longitude = $location['lng'] ?? null;
        }
    }

    public function getFullAddressAttribute()
    {
        return $this->street.' - '.$this->municipality.' ('.$this->province.')';
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
