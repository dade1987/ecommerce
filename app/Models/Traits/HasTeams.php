<?php

namespace App\Models\Traits;

use Filament\Panel;
use App\Models\Team;
use App\Models\TeamMorph;
use Filament\Facades\Filament;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasTeams
{

    public function canAccessTenant(Model $tenant): bool
    {

        return $this->teams->contains($tenant);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    public function teams(): MorphToMany
    {
        $pivot_class = TeamMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphToMany(Team::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }

    public function team()
    {
        $pivot_class = TeamMorph::class;
        $pivot = app($pivot_class);
        $pivot_table = $pivot->getTable();
        $pivot_fields = $pivot->getFillable();


        return $this->morphToMany(Team::class, 'model', $pivot_table)
            ->using($pivot_class)
            ->withPivot($pivot_fields)
            ->withTimestamps();
    }
}
