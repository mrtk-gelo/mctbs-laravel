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

    'mctbs' => [
        'gcash_qr_image' => env('GCASH_QR_IMAGE'),
        'bank_name' => env('BANK_NAME', env('BDO_BANK_NAME', env('BDO_ACCOUNT_NAME') ? 'BDO' : null)),
        'bank_account_name' => env('BANK_ACCOUNT_NAME', env('BDO_ACCOUNT_NAME')),
        'bank_account_number' => env('BANK_ACCOUNT_NUMBER', env('BDO_ACCOUNT_NUMBER')),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
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