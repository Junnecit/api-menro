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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/api/auth/google/callback'),

        // New Google sign-ins are only allowed to create an account when the
        // email is in this list or falls under one of the allowed domains.
        // Comma-separated in .env. Outside local/testing, empty allowlists
        // reject NEW Google accounts; existing users can still sign in.
        'allowed_emails' => array_filter(array_map(
            fn ($email) => strtolower(trim($email)),
            explode(',', env('GOOGLE_ALLOWED_EMAILS', ''))
        )),
        'allowed_domains' => array_filter(array_map(
            fn ($domain) => strtolower(trim($domain, " \t\n\r\0\x0B@")),
            explode(',', env('GOOGLE_ALLOWED_DOMAINS', ''))
        )),
    ],

    'ocr_space' => [
        'api_key' => env('OCR_SPACE_API_KEY'),
    ],

];
