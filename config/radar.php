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
    | Dashboard
    |--------------------------------------------------------------------------
    |
    | Radar's dashboard is intended for local/non-production inspection by
    | default. Production applications can still run scans and notifications;
    | explicitly opt in if you want to expose the dashboard there.
    |
    */

    'dashboard' => [
        'enabled' => env('RADAR_DASHBOARD_ENABLED', ! app()->isProduction()),
    ],

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
    | Pruning
    |--------------------------------------------------------------------------
    |
    | Radar will automatically prune scan records older than the configured
    | number of days. Set to null to disable pruning.
    |
    */

    'prune' => [
        'days' => env('RADAR_PRUNE_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Timeout
    |--------------------------------------------------------------------------
    |
    | Radar runs read-only Composer and Node package manager commands when
    | fixture JSON files are not present. This value controls how long each
    | command may run before Radar stops waiting for output.
    |
    */

    'command_timeout' => env('RADAR_COMMAND_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Radar may notify you when security vulnerabilities are found.
    |
    */

    'notifications' => [
        'dedupe_ttl' => env('RADAR_NOTIFICATION_DEDUPE_TTL', 86400),

        'routes' => [
            'mail' => array_values(array_filter(explode(',', (string) env('RADAR_NOTIFICATION_MAIL_TO', '')))),
            'slack' => env('RADAR_NOTIFICATION_SLACK_WEBHOOK_URL'),
        ],
    ],
];
