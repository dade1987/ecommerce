<?php

namespace Modules\Avatar3DReact\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Avatar3DReact\Services\AzureTTSService;

class Avatar3DReactServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/avatar3d.php',
            'avatar3d'
        );

        // Register TTS Service as singleton
        $this->app->singleton(AzureTTSService::class, function ($app) {
            return new AzureTTSService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register views with namespace 'avatar3d::'
        $this->loadViewsFrom(
            __DIR__ . '/../resources/views',
            'avatar3d'
        );

        // Register API routes
        $this->registerRoutes();

        // PageBlock registrato in config/filament-fabricator.php

        // Publish config (optional)
        $this->publishes([
            __DIR__ . '/../config/avatar3d.php' => config_path('avatar3d.php'),
        ], 'avatar3d-config');

        // Publish views (optional)
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/avatar3d'),
        ], 'avatar3d-views');

        // Publish assets (optional)
        $this->publishes([
            __DIR__ . '/../resources/assets' => public_path('avatar3d'),
        ], 'avatar3d-assets');
    }

    /**
     * Register API routes
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../routes/api.php');
    }
}