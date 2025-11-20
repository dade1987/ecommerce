<?php

namespace App\Providers\Filament;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GanttChartAssetProvider extends PackageServiceProvider
{
    public static string $name = 'gantt-chart-assets';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            assets: [
                Css::make('frappe-gantt-css', public_path('node_modules/frappe-gantt/dist/frappe-gantt.css')),
                Js::make('gantt-script', resource_path('js/gantt.js')),
            ],
            package: 'cavallini/axelle'
        );
    }
}
