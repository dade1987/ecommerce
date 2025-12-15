<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Speech / Transcription Provider
    |--------------------------------------------------------------------------
    |
    | Provider selezionabile per la trascrizione audio (Speech-to-Text).
    |
    | Valori supportati:
    | - openai_whisper (default, comportamento attuale)
    | - groq_whisper   (endpoint OpenAI-compatibile di Groq)
    */
    'transcription' => [
        'provider' => env('SPEECH_TRANSCRIPTION_PROVIDER', 'openai_whisper'),

        // OpenAI Whisper (attuale)
        'openai' => [
            'base_url' => env('OPENAI_WHISPER_BASE_URL', 'https://api.openai.com/v1/'),
            'model' => env('OPENAI_WHISPER_MODEL', 'whisper-1'),
            'timeout' => (int) env('OPENAI_WHISPER_TIMEOUT', 60),
        ],

        // Groq Whisper (OpenAI compatible)
        'groq' => [
            'base_url' => env('GROQ_WHISPER_BASE_URL', 'https://api.groq.com/openai/v1/'),
            'model' => env('GROQ_WHISPER_MODEL', 'whisper-large-v3-turbo'),
            'timeout' => (int) env('GROQ_WHISPER_TIMEOUT', 60),
        ],
    ],
];
