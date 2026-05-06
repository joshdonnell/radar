<?php

declare(strict_types=1);

use JoshDonnell\Radar\Http\Middleware\Authorize;
use JoshDonnell\Radar\Support\Config;

it('falls back to the default dashboard path for invalid path config', function (): void {
    config()->set('radar.path', ['radar']);

    expect(Config::path())->toBe('radar');
});

it('always includes the radar authorization middleware', function (): void {
    config()->set('radar.middleware', ['web']);

    expect(Config::routeMiddleware())->toBe(['web', Authorize::class]);
});

it('publishes a command timeout', function (): void {
    expect(config('radar.command_timeout'))->toBe(60);
});

it('preconfigures nightly notification scans', function (): void {
    expect(config('radar.notifications.schedule'))->toMatchArray([
        'enabled' => true,
        'time' => '02:00',
        'timezone' => null,
    ]);
});
