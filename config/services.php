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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'wompi' => [
        'env' => env('WOMPI_ENV', 'sandbox'),
        'base_url' => env('WOMPI_BASE_URL', 'https://sandbox.wompi.co/v1'),
        'public_key' => env('WOMPI_PUBLIC_KEY', 'pub_test_dummy'),
        'private_key' => env('WOMPI_PRIVATE_KEY', 'prv_test_dummy'),
        'integrity_secret' => env('WOMPI_INTEGRITY_SECRET', 'test_integrity_dummy'),
        'events_secret' => env('WOMPI_EVENTS_SECRET', 'test_events_dummy'),
        'dias_gracia' => (int) env('WOMPI_DIAS_GRACIA', 5),
    ],

];
