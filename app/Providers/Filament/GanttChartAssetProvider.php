<?php

namespace App\Providers\Filament;

use Filament\Http\Assets\Asset;
use Filament\Http\Assets\Css;
use Filament\Http\Assets\Js;
use Filament\PluginServiceProvider;
use Filament\Support\Assets\Theme;

class GanttChartAssetProvider extends PluginServiceProvider
{
    public function register(Container $container): void
    {
        // Not used
    }

    public function boot(Container $container): void
    {
        FilamentAsset::register([
            Css::make('frappe-gantt-css', public_path('node_modules/frappe-gantt/dist/frappe-gantt.css')),
            Js::make('gantt-script', resource_path('js/gantt.js')),
        ], 'cavallini/axelle');
    }
} 