<?php

return [
    'bulksmslive' => [
        'base_url' => env('BULKSMSLIVE_BASE_URL', 'https://api.bulksmslive.com'),
        'api_key' => env('BULKSMSLIVE_API_KEY'),
        'sender_name' => env('BULKSMSLIVE_SENDER_NAME', 'SchoolOS'),
        'force_dnd' => env('BULKSMSLIVE_FORCE_DND', true),
        'default_country_code' => env('BULKSMSLIVE_DEFAULT_COUNTRY_CODE', '234'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'private_key' => env('FIREBASE_PRIVATE_KEY'),
    ],

    'ai' => [
        'base_url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
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
