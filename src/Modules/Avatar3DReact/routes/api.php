<?php

use Illuminate\Support\Facades\Route;
use Modules\Avatar3DReact\Http\Controllers\Avatar3DTTSController;

/*
|--------------------------------------------------------------------------
| Avatar3D React API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('avatar3d')->group(function () {
    // TTS endpoint - converts text to speech with viseme data
    Route::post('/tts', [Avatar3DTTSController::class, 'talk'])
        ->name('avatar3d.tts');

    // Cleanup endpoint (for scheduled tasks or manual cleanup)
    Route::post('/cleanup', [Avatar3DTTSController::class, 'cleanup'])
        ->name('avatar3d.cleanup')
        ->middleware('auth:sanctum'); // Protect cleanup endpoint
});