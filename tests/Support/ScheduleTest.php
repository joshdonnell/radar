<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use JoshDonnell\Radar\RadarServiceProvider;

it('schedules notification scans every night', function (): void {
    app()->forgetInstance(Schedule::class);

    (new RadarServiceProvider(app()))->packageBooted();

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event): bool => str_contains((string) $event->command, 'radar:notify --scan'));

    expect($event)
        ->not->toBeNull()
        ->expression->toBe('0 2 * * *')
        ->withoutOverlapping->toBeTrue();
});

it('does not schedule notification scans when disabled', function (): void {
    config()->set('radar.notifications.schedule.enabled', false);
    app()->forgetInstance(Schedule::class);

    (new RadarServiceProvider(app()))->packageBooted();

    expect(collect(app(Schedule::class)->events())
        ->contains(fn ($event): bool => str_contains((string) $event->command, 'radar:notify --scan')))->toBeFalse();
});
