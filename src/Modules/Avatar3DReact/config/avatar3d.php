<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Azure Speech Services Configuration
    |--------------------------------------------------------------------------
    */
    'azure' => [
        'speech_key' => env('AZURE_SPEECH_KEY', ''),
        'speech_region' => env('AZURE_SPEECH_REGION', 'italynorth'),
        'default_voice' => env('AZURE_DEFAULT_VOICE', 'it-IT-ElsaNeural'),
    ],

    /*
    |--------------------------------------------------------------------------
    | TTS Service Configuration
    |--------------------------------------------------------------------------
    |
    | Choose how to run the TTS service:
    | - tts_service_url: URL of external Node.js TTS microservice
    | - use_node_process: If true, runs Node.js script directly (requires Node installed)
    |
    */
    'tts_service_url' => env('AVATAR3D_TTS_SERVICE_URL', 'http://localhost:8000'),
    'use_node_process' => env('AVATAR3D_USE_NODE_PROCESS', false),

    /*
    |--------------------------------------------------------------------------
    | Avatar Default Settings
    |--------------------------------------------------------------------------
    */
    'avatar' => [
        'model_url' => env('AVATAR3D_MODEL_URL', '/avatar3d/models/avatar.glb'),
        'default_animation' => 'Idle',
    ],
];