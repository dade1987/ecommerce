<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Product;
use App\Models\Category;
use App\Policies\PagePolicy;
use App\Policies\MediaPolicy;
use App\Policies\ProductPolicy;
use App\Policies\CategoryPolicy;
use Awcodes\Curator\Models\Media;
use Z3d0X\FilamentFabricator\Models\Page;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
