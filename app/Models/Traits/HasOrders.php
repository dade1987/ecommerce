<?php

namespace App\Models\Traits;

use App\Models\Order;
use App\Models\OrderMorph;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasOrders
{
    public function orders(): MorphToMany
    {
        $pivot_class = OrderMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();

        return $this->morphToMany(Order::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }
}
