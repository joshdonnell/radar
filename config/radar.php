<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable Radar entirely.
    |
    */

    'enabled' => env('RADAR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Radar will be accessible from.
    |
    */

    'path' => env('RADAR_PATH', 'radar'),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    |
    | Radar stores scan snapshots in the database. You may use a separate
    | connection if you prefer to keep Radar's data away from your application
    | tables. The table name is fixed by Radar's published migration.
    |
    */

    'storage' => [
        'database' => [
            'connection' => env('RADAR_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Radar route.
    |
    */

    'middleware' => [
        'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | This gate determines who can access Radar outside of local environments.
    |
    */

    'authorization' => [
        'gate' => 'viewRadar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Radar may notify you when security vulnerabilities are found.
    |
    */

    'notifications' => [
        'mail' => [
            'to' => array_values(array_filter(explode(',', (string) env('RADAR_MAIL_TO', '')))),
        ],

        'slack' => [
            'webhook_url' => env('RADAR_SLACK_WEBHOOK_URL'),
        ],
    ],
];
