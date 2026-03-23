<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Assist Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the OpenAI-compatible LLM endpoint used for ticket
    | summarisation, KB article suggestions, and draft reply generation.
    |
    */

    'endpoint' => env('AI_ASSIST_ENDPOINT', 'https://api.openai.com/v1'),
    'api_key'  => env('AI_ASSIST_API_KEY', ''),
    'model'    => env('AI_ASSIST_MODEL', 'gpt-4o-mini'),
];
