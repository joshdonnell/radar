# Configuration

Radar publishes a `config/radar.php` file so applications can adjust the dashboard route, dashboard availability, storage connection, authorization gate, middleware, and notification targets.

Publish the configuration with:

```bash
php artisan vendor:publish --tag="radar-config"
```

## Enabled

```php
'enabled' => env('RADAR_ENABLED', true),
```

Set `RADAR_ENABLED=false` to disable Radar.

## Dashboard path

```php
'path' => env('RADAR_PATH', 'radar'),
```

By default, the dashboard is available at `/radar`.

## Dashboard availability

```php
'dashboard' => [
    'enabled' => env('RADAR_DASHBOARD_ENABLED', ! app()->isProduction()),
],
```

The dashboard is enabled outside production by default and disabled in production by default. Production applications can still run scans and send notifications.

## Storage connection

```php
'storage' => [
    'database' => [
        'connection' => env('RADAR_DB_CONNECTION', env('DB_CONNECTION', 'sqlite')),
    ],
],
```

Radar stores scan snapshots in your database. Set `RADAR_DB_CONNECTION` if you want Radar to use a different connection from the application default.

## Command timeout

```php
'command_timeout' => env('RADAR_COMMAND_TIMEOUT', 60),
```

Radar runs read-only Composer and Node package manager commands when scan fixture JSON files are not present. Increase this value if dependency commands need more time in your environment.

## Middleware

```php
'middleware' => [
    'web',
],
```

These middleware run on Radar routes.

## Authorization

```php
'authorization' => [
    'gate' => 'viewRadar',
],
```

Outside local environments, Radar checks this gate before serving the dashboard.

Define it in your application, for example:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewRadar', fn ($user = null) => app()->environment('local'));
```

## Notifications

```php
'notifications' => [
    'dedupe_ttl' => env('RADAR_NOTIFICATION_DEDUPE_TTL', 86400),

    'schedule' => [
        'enabled' => env('RADAR_NOTIFICATION_SCHEDULE_ENABLED', true),
        'time' => env('RADAR_NOTIFICATION_SCHEDULE_TIME', '02:00'),
        'timezone' => env('RADAR_NOTIFICATION_SCHEDULE_TIMEZONE'),
    ],

    'routes' => [
        'mail' => array_values(array_filter(explode(',', (string) env('RADAR_NOTIFICATION_MAIL_TO', '')))),
        'slack' => env('RADAR_NOTIFICATION_SLACK_WEBHOOK_URL'),
    ],
],
```

Radar uses Laravel Notifications. Your application still owns the normal mail and Slack transport configuration; Radar only stores the on-demand notification routes it should target.

- `routes.mail` is a list of mail recipients.
- `routes.slack` is a Slack webhook URL for Laravel's Slack notification channel.
- `dedupe_ttl` controls how long identical vulnerability finding sets are suppressed after successful delivery.
- `schedule.enabled` controls Radar's built-in scheduled `radar:notify --scan` run.
- `schedule.time` controls the nightly run time, using Laravel scheduler time format.
- `schedule.timezone` optionally pins the scheduled run to a timezone.

Radar only sends vulnerability notifications when at least one route is configured. The scheduled run is registered automatically, but your application still needs Laravel's scheduler running in production.
