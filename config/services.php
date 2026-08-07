<?php

return [

    'payments' => [
        'default' => env('PAYMENT_PROVIDER', 'fake'),
        'fake_secret' => env('FAKE_PAYMENT_SECRET', 'lamari-local-fake-secret'),
        'mono_token' => env('MONO_MERCHANT_TOKEN'),
        'mono_tokens' => [
            'mono' => env('MONO_FOP2_MERCHANT_TOKEN', env('MONO_MERCHANT_TOKEN')),
            'privat' => env('MONO_FOP3_MERCHANT_TOKEN'),
        ],
        'mono_base_url' => env('MONO_API_BASE_URL', 'https://api.monobank.ua'),
        'mono_public_key' => env('MONO_WEBHOOK_PUBLIC_KEY'),
        'mono_public_keys' => [
            'mono' => env('MONO_FOP2_WEBHOOK_PUBLIC_KEY', env('MONO_WEBHOOK_PUBLIC_KEY')),
            'privat' => env('MONO_FOP3_WEBHOOK_PUBLIC_KEY'),
        ],
        'wayforpay_url' => env('WAYFORPAY_URL', 'https://secure.wayforpay.com/pay'),
        'wayforpay_domain' => env('WAYFORPAY_DOMAIN', 'test.lamari.jewelry'),
        'wayforpay_merchants' => (static function (): array {
            $encoded = (string) env('WAYFORPAY_MERCHANTS_B64', '');
            if ($encoded !== '') {
                $decoded = base64_decode($encoded, true);
                $merchants = $decoded === false ? null : json_decode($decoded, true);

                return is_array($merchants) ? $merchants : [];
            }

            $merchants = json_decode((string) env('WAYFORPAY_MERCHANTS_JSON', '{}'), true);

            return is_array($merchants) ? $merchants : [];
        })(),
    ],

    'salesdrive' => [
        'enabled' => env('SALESDRIVE_ENABLED', false),
        'base_url' => env('SALESDRIVE_BASE_URL', 'https://lamari.salesdrive.me'),
        'source' => env('SALESDRIVE_SOURCE', 'test.lamari.jewelry'),
        'orders_key' => env('SALESDRIVE_ORDERS_API_KEY'),
        'payments_key' => env('SALESDRIVE_PAYMENTS_API_KEY'),
        'pending_status' => env('SALESDRIVE_PENDING_STATUS', 'Оплата'),
        'paid_status' => env('SALESDRIVE_PAID_STATUS', 'Підтверджено'),
        'deposit_status' => env('SALESDRIVE_DEPOSIT_STATUS', 'Підтверджено'),
        'payment_method' => env('SALESDRIVE_PAYMENT_METHOD', 'Оплата карткою на сайті'),
        'delivery_method' => env('SALESDRIVE_DELIVERY_METHOD', 'Нова Пошта'),
        'organization_id' => env('SALESDRIVE_ORGANIZATION_ID'),
        'account_number' => env('SALESDRIVE_ACCOUNT_NUMBER'),
    ],

    'telegram_orders' => [
        'enabled' => env('TELEGRAM_ORDERS_ENABLED', false),
        'bot_token' => env('TELEGRAM_ORDERS_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_ORDERS_CHAT_ID'),
        'source' => env('TELEGRAM_ORDERS_SOURCE', 'test.lamari.jewelry'),
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
