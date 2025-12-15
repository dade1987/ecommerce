<?php

namespace App\Providers;

use App\Components\QuoterComponent;
use App\Contracts\SpeechTranscriptionProviderInterface;
use App\Services\Speech\GroqWhisperTranscriptionProvider;
use App\Services\Speech\OpenAiWhisperTranscriptionProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('path.public', function () {
            return base_path('public_html');
        });

        $this->app->singleton(SpeechTranscriptionProviderInterface::class, function () {
            $provider = (string) config('speech.transcription.provider', 'openai_whisper');

            return match ($provider) {
                'groq_whisper' => new GroqWhisperTranscriptionProvider(),
                'openai_whisper' => new OpenAiWhisperTranscriptionProvider(),
                default => new OpenAiWhisperTranscriptionProvider(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
