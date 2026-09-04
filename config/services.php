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

    'image_processor' => [
        'url' => env('IMAGE_PROCESSOR_URL', 'http://127.0.0.1:8001'),
        'api_token' => env('IMAGE_PROCESSOR_API_TOKEN', 'change-me'),
        'callback_token' => env('IMAGE_PROCESSOR_CALLBACK_TOKEN'),
        'disk' => env('IMAGE_PROCESSOR_DISK', 'public'),
        'model' => env('IMAGE_PROCESSOR_MODEL', 'opencv-hog-face-v1'),
        'embedding_dimensions' => (int) env('IMAGE_PROCESSOR_EMBEDDING_DIMENSIONS', 512),
        'max_faces' => (int) env('IMAGE_PROCESSOR_MAX_FACES', 20),
        'timeout' => (int) env('IMAGE_PROCESSOR_TIMEOUT', 45),
    ],
];
