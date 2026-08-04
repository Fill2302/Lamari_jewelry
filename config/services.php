<?php

return [

    'payments' => [
        'default' => env('PAYMENT_PROVIDER', 'fake'),
        'fake_secret' => env('FAKE_PAYMENT_SECRET', 'lamari-local-fake-secret'),
        'mono_test_mode' => env('MONO_PAYMENT_TEST_MODE', true),
        'mono_token' => env('MONO_MERCHANT_TOKEN_TEST'),
        'mono_base_url' => env('MONO_API_BASE_URL', 'https://api.monobank.ua'),
        'mono_public_key' => env('MONO_WEBHOOK_PUBLIC_KEY'),
    ],

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

];
