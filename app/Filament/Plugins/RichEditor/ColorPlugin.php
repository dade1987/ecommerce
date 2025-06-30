<?php

namespace App\Filament\Plugins\RichEditor;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

class ColorPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-rich-editor-color-plugin';
    }

    public function register(Panel $panel): void
    {
        // Non registriamo nulla qui per ora
    }

    public function boot(Panel $panel): void
    {
        FilamentAsset::register([
            Js::make('rich-editor-color-plugin', asset('build/assets/filament/rich-editor-plugins/color.js'))->loadedOnRequest(),
        ], 'filament/forms');
    }

    public static function make(): static
    {
        return app(static::class);
    }
} 