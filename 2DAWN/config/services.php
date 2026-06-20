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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'signing_secret' => env('SLACK_SIGNING_SECRET'),
        ],
    ],

'paystack' => [
        'public' => env('PAYSTACK_PUBLIC_KEY'),
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'callback_url' => env('PAYSTACK_CALLBACK_URL'),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret' => env('TURNSTILE_SECRET_KEY'),
    ],

];
