<p align="center">
    <h1 align="center">Laravel Radar</h1>
    <p align="center">
        <a href="https://github.com/JoshDonnell/radar/actions/workflows/tests.yml"><img alt="Tests" src="https://github.com/JoshDonnell/radar/actions/workflows/tests.yml/badge.svg"></a>
        <a href="https://github.com/JoshDonnell/radar/actions/workflows/formats.yml"><img alt="Formats" src="https://github.com/JoshDonnell/radar/actions/workflows/formats.yml/badge.svg"></a>
        <a href="https://github.com/JoshDonnell/radar/blob/main/LICENSE.md"><img alt="License" src="https://img.shields.io/badge/license-MIT-blue.svg"></a>
    </p>
</p>

## Introduction

**Laravel Radar** is a lightweight dependency health dashboard for Laravel applications.

It helps you answer one question:

> Do I have vulnerable Composer or NPM packages, and what should I do next?

Radar is inspired by Laravel Telescope: install it, run a scan, open an internal dashboard, and get a clear dependency health snapshot without leaving your app.

Radar is intentionally read-only. It reports vulnerable, outdated, and abandoned packages, then suggests conservative next steps. It does **not** update dependencies, edit lock files, commit changes, or deploy code for you.

## Installation

> **Requires [PHP 8.3+](https://php.net/releases/)** and **Laravel 12+**.

Install Radar with Composer:

```bash
composer require joshdonnell/radar
```

Publish Radar's config file, migration, and dashboard assets:

```bash
php artisan radar:install
```

Run the migration:

```bash
php artisan migrate
```

## Usage

Run your first dependency scan:

```bash
php artisan radar:scan
```

Open the dashboard:

```txt
/radar
```

Before the first scan, the dashboard shows a first-run screen with a **Run first scan** button. After a scan has been recorded, Radar shows the latest dependency health snapshot.

> **Production dashboard default:** Radar's dashboard is disabled in production by default. Production applications can still run scans and send notifications. Set `RADAR_DASHBOARD_ENABLED=true` only when the dashboard is protected by trusted authentication and authorization.

## Dashboard

Radar's dashboard shows:

- health score
- latest scan time
- Composer and NPM package inventory
- vulnerable packages with advisory details
- direct vs transitive dependency status
- affected and patched versions where available
- safe suggested commands or next steps
- outdated direct dependencies
- abandoned Composer packages

The dashboard path defaults to `/radar` and can be changed with:

```env
RADAR_PATH=internal/radar
```

## Commands

### Scan dependencies

```bash
php artisan radar:scan
```

Scan another project path:

```bash
php artisan radar:scan --path=/path/to/app
```

### List vulnerabilities

```bash
php artisan radar:vulnerabilities
```

Lists Composer and NPM vulnerability findings detected for the project.

### List outdated dependencies

```bash
php artisan radar:outdated
```

Lists outdated direct Composer and NPM dependencies.

### Recalculate scores

```bash
php artisan radar:score
php artisan radar:score --all
```

Recalculates stored scan health scores.

### Send notifications

```bash
php artisan radar:notify
```

Sends deduplicated vulnerability notifications for the latest stored scan using Laravel Notifications.

Run a fresh scan before notifying:

```bash
php artisan radar:notify --scan
```

### Clear scan history

```bash
php artisan radar:clear
php artisan radar:clear --force
```

Clears stored scan snapshots.

## Composer and NPM support

Radar reads dependency information from package manager files and installed package metadata.

Composer support includes:

- package inventory from `composer.lock`
- fallback inventory from `vendor/composer/installed.json`
- vulnerability findings from `composer audit --format=json`
- outdated direct dependencies from Composer's outdated output
- abandoned package metadata from Composer package data

NPM ecosystem support includes:

- package inventory from `package-lock.json`
- fallback direct package inventory from `node_modules/*/package.json`
- vulnerability findings from `npm audit --json`
- outdated direct dependencies from NPM's outdated output

## Supported Node runners

Radar detects the JavaScript package manager from the project lock file and uses that runner when suggesting safe NPM ecosystem update commands.

| Lock file | Runner | Example recommendation |
| --- | --- | --- |
| `package-lock.json` | npm | `npm update vite` |
| `npm-shrinkwrap.json` | npm | `npm update vite` |
| `yarn.lock` | Yarn | `yarn up vite` |
| `pnpm-lock.yaml` | pnpm | `pnpm update vite` |
| `bun.lock` | Bun | `bun update vite` |
| `bun.lockb` | Bun | `bun update vite` |

If no known lock file exists, Radar falls back to npm.

## Notifications

Radar uses Laravel Notifications for vulnerability alerts. Your application still owns normal Laravel mail configuration; Radar only needs to know which on-demand notification routes to target.

Configure mail recipients:

```env
RADAR_NOTIFICATION_MAIL_TO=security@example.com,dev@example.com
```

Configure Slack:

```env
RADAR_NOTIFICATION_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
```

Then run:

```bash
php artisan radar:notify
```

Radar only sends notifications when vulnerabilities exist and at least one notification route is configured. Repeated notifications for the same finding set are deduplicated for the configured TTL.

## Scheduling

Radar preconfigures a nightly scheduled `radar:notify --scan` run at `02:00`, so each notification run starts with a fresh scan. In production, make sure your application has Laravel's scheduler running.

You may customize or disable the built-in schedule:

```env
RADAR_NOTIFICATION_SCHEDULE_ENABLED=true
RADAR_NOTIFICATION_SCHEDULE_TIME=02:00
RADAR_NOTIFICATION_SCHEDULE_TIMEZONE=Europe/London
```

The dashboard remains disabled in production unless you explicitly enable it.

## Authorization

Radar checks the configured gate outside local environments:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewRadar', fn ($user = null): bool => $user?->is_admin === true);
```

You may change the gate name in `config/radar.php`.

## Configuration

Publish the configuration file with:

```bash
php artisan vendor:publish --tag="radar-config"
```

Useful environment variables:

```env
RADAR_ENABLED=true
RADAR_PATH=radar
RADAR_DASHBOARD_ENABLED=false
RADAR_DB_CONNECTION=sqlite
RADAR_PRUNE_DAYS=30
RADAR_NOTIFICATION_MAIL_TO=security@example.com
RADAR_NOTIFICATION_SLACK_WEBHOOK_URL=
RADAR_NOTIFICATION_DEDUPE_TTL=86400
RADAR_NOTIFICATION_SCHEDULE_ENABLED=true
RADAR_NOTIFICATION_SCHEDULE_TIME=02:00
RADAR_NOTIFICATION_SCHEDULE_TIMEZONE=
```

See [the configuration documentation](docs/configuration.md) for the full config reference.

## Testing

Run the PHP test suite:

```bash
composer test
```

Run frontend checks:

```bash
npm run test:lint
npm run test:types
npm run build
```

## License

Laravel Radar is open-sourced software licensed under the [MIT license](LICENSE.md).
