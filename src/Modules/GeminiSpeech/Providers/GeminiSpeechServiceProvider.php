<?php

namespace Modules\GeminiSpeech\Providers;

use Illuminate\Support\ServiceProvider;

class GeminiSpeechServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Config di base del modulo
        $this->mergeConfigFrom(
            __DIR__.'/../config/geminispeech.php',
            'geminispeech'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Abilitazione/disabilitazione modulo via config('application.modules.gemini_speech')
        if (! config('application.modules.gemini_speech', true)) {
            return;
        }

        // Rotte API del modulo
        if (file_exists(__DIR__.'/../routes/geminispeech_routes.php')) {
            $this->loadRoutesFrom(__DIR__.'/../routes/geminispeech_routes.php');
        }

        // In futuro: loadMigrationsFrom, loadViewsFrom, ecc. se serviranno.
    }
}


