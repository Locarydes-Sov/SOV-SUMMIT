<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 300),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
    ],

    'localrydes' => [
        'base_url' => rtrim((string) env('LOCALRYDES_API_BASE_URL', 'https://www.localrydes.com/api/v2/external'), '/'),
        'key' => env('LOCALRYDES_API_KEY'),
        'timeout' => (int) env('LOCALRYDES_API_TIMEOUT', 10),
        'verify_ssl' => (bool) env('LOCALRYDES_API_VERIFY_SSL', true),
        'cache_ttl' => (int) env('LOCALRYDES_API_CACHE_TTL', 300),
        // Website used for the payment page: {web_url}/reservation-payment-create/{reservation uuid}
        'web_url' => rtrim((string) env('LOCALRYDES_WEB_URL', 'https://www.localrydes.com'), '/'),
        'payment_redirect_seconds' => (int) env('LOCALRYDES_PAYMENT_REDIRECT_SECONDS', 3),
    ],

];
