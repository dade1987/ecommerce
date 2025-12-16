<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    'vllm' => [
        // Esempio: https://51iprziqf9r25z-8000.proxy.runpod.net/v1
        'base_uri' => env('VLLM_BASE_URI', ''),
        'key' => env('VLLM_API_KEY', ''),
        'model' => env('VLLM_MODEL', 'gpt-4o-mini'),
    ],

    'groq' => [
        // Groq OpenAI-compatible API
        'base_uri' => env('GROQ_BASE_URI', 'https://api.groq.com/openai/v1'),
        'key' => env('GROQ_API_KEY', ''),
        'model' => env('GROQ_MODEL', 'llama-3.1-70b-versatile'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    'google' => [
        'maps' => [
            'api_key' => env('GOOGLE_MAPS_API_KEY'),
        ],
    ],

    'heygen' => [
        'api_key' => env('HEYGEN_API_KEY'),
        'server_url' => env('HEYGEN_SERVER_URL', 'https://api.heygen.com'),
    ],

    'liveavatar' => [
        'api_key' => env('LIVEAVATAR_API_KEY'),
        'server_url' => env('LIVEAVATAR_SERVER_URL', 'https://api.liveavatar.com'),
    ],

];
