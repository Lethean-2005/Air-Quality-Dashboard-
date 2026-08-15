<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // '*' is invalid together with supports_credentials=true (browsers reject the response
    // outright), so this lists real origins instead: local dev plus whatever FRONTEND_URL is
    // set to per-environment (the Vercel deployment URL in production).
    'allowed_origins' => array_values(array_unique(array_filter([
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        env('FRONTEND_URL'),
    ]))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
