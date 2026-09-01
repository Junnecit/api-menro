<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_unique(array_filter([
        // Web SPA (Vite dev server)
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        // Mobile Expo web target (if running via browser)
        env('MOBILE_URL', 'http://localhost:8081'),
        'http://localhost:8081',
        // LAN IP for physical devices / LAN dev access
        'http://192.168.8.34:5173',
        'http://192.168.8.34:8081',
        'http://192.168.8.36:5173',
        'http://192.168.8.36:8081',
    ])),

    'allowed_origins_patterns' => [
        '#^https?://(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+|172\.(1[6-9]|2[0-9]|3[0-1])\.\d+\.\d+)(:\d+)?$#',
        '#^https?://.*\.ngrok(-free)?\.(app|dev)$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required for cookie/session auth (Sanctum SPA). Bearer-token clients
    // (mobile) are unaffected; they never send credentials cookies anyway.
    'supports_credentials' => true,

];

