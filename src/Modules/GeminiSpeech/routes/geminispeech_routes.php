<?php

use Illuminate\Support\Facades\Route;
use Modules\GeminiSpeech\Http\Controllers\GeminiSpeechTranscriptionController;

/*
|--------------------------------------------------------------------------
| Gemini Speech Module Routes
|--------------------------------------------------------------------------
|
| Qui definiamo le rotte specifiche del modulo GeminiSpeech.
| Vengono caricate dal GeminiSpeechServiceProvider.
|
*/

Route::middleware('api')
    ->prefix('api')
    ->group(function () {
        Route::post('/gemini-speech/transcribe', [GeminiSpeechTranscriptionController::class, 'transcribe']);
    });


