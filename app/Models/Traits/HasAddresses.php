<?php

namespace App\Models\Traits;

use App\Models\Address;
use App\Models\AddressMorph;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasAddresses
{
    public function addresses(): MorphToMany
    {
        $pivot_class = AddressMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();

        return $this->morphToMany(Address::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }
}
