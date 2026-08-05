<?php

return [

    'payments' => [
        'default' => env('PAYMENT_PROVIDER', 'fake'),
        'fake_secret' => env('FAKE_PAYMENT_SECRET', 'lamari-local-fake-secret'),
        'mono_token' => env('MONO_MERCHANT_TOKEN'),
        'mono_base_url' => env('MONO_API_BASE_URL', 'https://api.monobank.ua'),
        'mono_public_key' => env('MONO_WEBHOOK_PUBLIC_KEY'),
    ],

    'salesdrive' => [
        'enabled' => env('SALESDRIVE_ENABLED', false),
        'base_url' => env('SALESDRIVE_BASE_URL', 'https://lamari.salesdrive.me'),
        'source' => env('SALESDRIVE_SOURCE', 'test.lamari.jewelry'),
        'orders_key' => env('SALESDRIVE_ORDERS_API_KEY'),
        'payments_key' => env('SALESDRIVE_PAYMENTS_API_KEY'),
        'pending_status' => env('SALESDRIVE_PENDING_STATUS', 'Очікує оплати'),
        'paid_status' => env('SALESDRIVE_PAID_STATUS', 'Оплачено'),
        'payment_method' => env('SALESDRIVE_PAYMENT_METHOD', 'Онлайн-оплата'),
        'delivery_method' => env('SALESDRIVE_DELIVERY_METHOD', 'Нова Пошта'),
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
