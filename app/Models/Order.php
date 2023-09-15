<?php

namespace App\Models;

use App\Models\Traits\HasTeams;
use App\Models\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Order extends Model
{
    use HasFactory;

    use HasTeams;

    //custom traits
    use HasAddresses;

    protected $fillable = ['delivery_date'];

    public function products(): MorphToMany
    {
        $pivot_class = ProductMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphToMany(Product::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }
}
