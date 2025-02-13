<?php

namespace App\Models;

use App\Models\Address;
use App\Models\Category;
use App\Models\Traits\HasTeams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Team extends Model
{
    use HasFactory;
    use HasTeams;

    protected $fillable = ['welcome_message', 'logo', 'name', 'slug', 'nation', 'region', 'province', 'municipality', 'street', 'postal_code', 'phone'];

    public function members(): MorphToMany
    {
        $pivot_class = TeamMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();

        return $this->morphedByMany(User::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }

    /*
        public function categories(): MorphToMany
        {
            $pivot_class = TeamMorph::class;
            $pivot = app($pivot_class);
            $pivot_table = $pivot->getTable();
            $pivot_fields = $pivot->getFillable();


            return $this->morphedByMany(Category::class, 'model', $pivot_table)
                ->using($pivot_class)
                ->withPivot($pivot_fields)
                ->withTimestamps();
        }

        public function addresses(): MorphToMany
        {
            $pivot_class = TeamMorph::class;
            $pivot = app($pivot_class);
            $pivot_table = $pivot->getTable();
            $pivot_fields = $pivot->getFillable();


            return $this->morphedByMany(Address::class, 'model', $pivot_table)
                ->using($pivot_class)
                ->withPivot($pivot_fields)
                ->withTimestamps();
        }

        public function orders(): MorphToMany
        {
            $pivot_class = TeamMorph::class;
            $pivot = app($pivot_class);
            $pivot_table = $pivot->getTable();
            $pivot_fields = $pivot->getFillable();


            return $this->morphedByMany(Order::class, 'model', $pivot_table)
                ->using($pivot_class)
                ->withPivot($pivot_fields)
                ->withTimestamps();
        }

        public function products(): MorphToMany
        {
            $pivot_class = TeamMorph::class;
            $pivot = app($pivot_class);
            $pivot_table = $pivot->getTable();
            $pivot_fields = $pivot->getFillable();


            return $this->morphedByMany(Product::class, 'model', $pivot_table)
                ->using($pivot_class)
                ->withPivot($pivot_fields)
                ->withTimestamps();
        }*/

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
