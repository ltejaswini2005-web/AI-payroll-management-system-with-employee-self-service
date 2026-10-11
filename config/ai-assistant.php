<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "openai", "gemini", "mock"
    | If no API key is provided or external API fails, the system automatically
    | falls back to the built-in intelligent HR assistant engine.
    |
    */
    'provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('AI_API_KEY', ''),
        'model' => env('AI_MODEL', 'gpt-3.5-turbo'),
        'temperature' => (float) env('AI_TEMPERATURE', 0.4),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 800),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Gemini Configuration
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'temperature' => (float) env('AI_TEMPERATURE', 0.4),
        'max_tokens' => (int) env('AI_MAX_TOKENS', 800),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting & Safety
    |--------------------------------------------------------------------------
    */
    'rate_limit_per_minute' => 30,
    'max_prompt_length' => 1000,
];
