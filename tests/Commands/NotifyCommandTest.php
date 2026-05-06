<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Notifications\VulnerabilitiesFound;

beforeEach(function (): void {
    config(['cache.default' => 'array']);
    Cache::flush();
});

it('sends a notification for the latest vulnerable scan', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 finding(s) via mail.');

    Notification::assertSentOnDemand(VulnerabilitiesFound::class, fn (VulnerabilitiesFound $notification, array $channels): bool => $channels === ['mail']
        && $notification->channels === ['mail']
        && count($notification->notification->vulnerabilities) === 2);
});

it('sends to mail and slack when both routes are configured', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);
    Config::set('radar.notifications.routes.slack', 'https://hooks.slack.com/services/example');

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 finding(s) via mail, slack.');

    Notification::assertSentOnDemand(VulnerabilitiesFound::class, fn (VulnerabilitiesFound $notification, array $channels): bool => $channels === ['mail', 'slack']
        && $notification->channels === ['mail', 'slack']);
});

it('omits the dashboard url when the dashboard is disabled', function (): void {
    Config::set('radar.dashboard.enabled', false);
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 finding(s) via mail.');

    Notification::assertSentOnDemand(VulnerabilitiesFound::class, fn (VulnerabilitiesFound $notification): bool => $notification->notification->dashboardUrl === null);
});

it('exits early when no scan exists', function (): void {
    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No Radar scans to notify about.');
});

it('exits early when scan has no vulnerabilities', function (): void {
    RadarScan::create([
        'id' => 'e8a38a72-5bf4-4c93-89ea-013ef3d5f2c7',
        'score' => 100,
        'vulnerability_count' => 0,
        'package_count' => 10,
        'payload' => [
            'packages' => [],
            'vulnerabilities' => [],
            'outdated' => [],
            'abandoned' => [],
        ],
    ]);

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No vulnerabilities to notify about.');
});

it('exits early when no notification routes are configured', function (): void {
    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No Radar notification channels are configured.');

    Notification::assertNothingSent();
});

it('deduplicates notifications for the same finding set', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 finding(s) via mail.');

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Vulnerability notification already sent for this finding set.');

    Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 1);
});

function vulnerableScan(): RadarScan
{
    return RadarScan::create([
        'id' => 'e8a38a72-5bf4-4c93-89ea-013ef3d5f2c7',
        'score' => 80,
        'vulnerability_count' => 2,
        'package_count' => 10,
        'payload' => [
            'packages' => [],
            'vulnerabilities' => [
                [
                    'id' => 'vuln-1',
                    'ecosystem' => 'composer',
                    'package_name' => 'foo/bar',
                    'installed_version' => '1.0.0',
                    'severity' => 'high',
                    'advisory_id' => 'CVE-2025-0001',
                    'is_direct' => true,
                    'cve' => null,
                    'affected_versions' => '< 2.0',
                    'patched_version' => '2.0',
                    'advisory_url' => null,
                    'recommendation' => null,
                    'required_by' => [],
                ],
                [
                    'id' => 'vuln-2',
                    'ecosystem' => 'npm',
                    'package_name' => 'pkg',
                    'installed_version' => '3.0.0',
                    'severity' => 'medium',
                    'advisory_id' => 'GHSA-abcd',
                    'is_direct' => false,
                    'cve' => null,
                    'affected_versions' => null,
                    'patched_version' => null,
                    'advisory_url' => null,
                    'recommendation' => null,
                    'required_by' => [],
                ],
            ],
            'outdated' => [],
            'abandoned' => [],
        ],
    ]);
}
