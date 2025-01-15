<?php declare(strict_types=1);

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
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL', 'observer-service-alerts'),
        ],
    ],

    'rabbitmq' => [
        'host' => env('RABBITMQ_HOST', 'rabbitmq'),
        'port' => env('RABBITMQ_PORT', 5672),
        'username' => env('RABBITMQ_USERNAME', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'is_secure' => env('RABBITMQ_IS_SECURE', true),
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
            'capath' => null,
            'cafile' => null,
        ],
        'connection' => [
            'connection_timeout' => 60,
            'read_write_timeout' => 300,
            'heartbeat' => 60,
        ],
        'exchange' => 'STATUS_MONITORING_SERVICE_EXCHANGE',
        'queue' => 'STATUS_MONITORING_SERVICE_NOTIFICATIONS_QUEUE',
    ],

];
