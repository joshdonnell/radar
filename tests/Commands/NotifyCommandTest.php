<?php

declare(strict_types=1);

use Illuminate\Notifications\AnonymousNotifiable;
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
        ->expectsOutputToContain('Sent vulnerability notification for 2 new finding(s) via mail.');

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
        ->expectsOutputToContain('Sent vulnerability notification for 2 new finding(s) via mail, slack.');

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
        ->expectsOutputToContain('Sent vulnerability notification for 2 new finding(s) via mail.');

    Notification::assertSentOnDemand(VulnerabilitiesFound::class, fn (VulnerabilitiesFound $notification): bool => $notification->notification->dashboardUrl === null);
});

it('exits early when no scan exists', function (): void {
    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No Radar scans to notify about.');
});

it('exits early when scan has no vulnerabilities', function (): void {
    RadarScan::create([
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
    Config::set('radar.notifications.routes.mail', []);
    Config::set('radar.notifications.routes.slack');

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No Radar notification channels are configured.');

    Notification::assertNothingSent();
});

it('only notifies about each vulnerability once', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 new finding(s) via mail.');

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No new vulnerabilities to notify about.');

    Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 1);
});

it('notifies about new vulnerabilities without repeating known ones', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    vulnerableScan(extraVulnerabilities: [
        vulnerabilityRecord(id: 'vuln-3', advisoryId: 'GHSA-new', severity: 'critical'),
    ]);

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 1 new finding(s) via mail.');

    Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 2);
    Notification::assertSentOnDemand(
        VulnerabilitiesFound::class,
        fn (VulnerabilitiesFound $notification): bool => count($notification->notification->vulnerabilities) === 1
            && $notification->notification->vulnerabilities[0]->advisoryId === 'GHSA-new',
    );
});

it('does not re-notify the remaining vulnerabilities when one is resolved', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    vulnerableScan(vulnerabilities: [
        vulnerabilityRecord(id: 'vuln-1', advisoryId: 'CVE-2025-0001', severity: 'high'),
    ]);

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No new vulnerabilities to notify about.');

    Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 1);
});

it('notifies again about a vulnerability that comes back after being resolved', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    vulnerableScan(vulnerabilities: []);

    $this->artisan('radar:notify')->assertSuccessful();

    vulnerableScan();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 new finding(s) via mail.');

    Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 2);
});

it('reminds about unresolved vulnerabilities after the configured interval', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);
    Config::set('radar.notifications.remind_after', '3600');

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    $this->travel(30)->minutes();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('No new vulnerabilities to notify about.');

    $this->travel(31)->minutes();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 2 new finding(s) via mail.');

    Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 2);
});

it('skips vulnerabilities below the minimum notification severity', function (): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);
    Config::set('radar.notifications.min_severity', 'high');

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')
        ->assertSuccessful()
        ->expectsOutputToContain('Sent vulnerability notification for 1 new finding(s) via mail.');

    Notification::assertSentOnDemand(
        VulnerabilitiesFound::class,
        fn (VulnerabilitiesFound $notification): bool => $notification->notification->vulnerabilities[0]->severity->value === 'high',
    );
});

it('includes vulnerabilities of unknown severity only at the lowest minimum severity', function (string $minimumSeverity, bool $notified): void {
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);
    Config::set('radar.notifications.min_severity', $minimumSeverity);

    vulnerableScan(vulnerabilities: [
        vulnerabilityRecord(id: 'vuln-unknown', advisoryId: 'GHSA-unknown', severity: 'unknown'),
    ]);

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    $notified
        ? Notification::assertSentOnDemandTimes(VulnerabilitiesFound::class, 1)
        : Notification::assertNothingSent();
})->with([
    'low' => ['low', true],
    'medium' => ['medium', false],
]);

it('trims mail recipients from a comma separated string', function (): void {
    Config::set('radar.notifications.routes.mail', 'dev@example.com, security@example.com ,');

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    Notification::assertSentOnDemand(
        VulnerabilitiesFound::class,
        fn (VulnerabilitiesFound $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routeNotificationFor('mail') === ['dev@example.com', 'security@example.com'],
    );
});

it('links to the trimmed dashboard path', function (): void {
    Config::set('radar.dashboard.enabled', true);
    Config::set('radar.path', '/internal/radar/');
    Config::set('radar.notifications.routes.mail', ['dev@example.com']);

    vulnerableScan();

    Notification::fake();

    $this->artisan('radar:notify')->assertSuccessful();

    Notification::assertSentOnDemand(
        VulnerabilitiesFound::class,
        fn (VulnerabilitiesFound $notification): bool => $notification->notification->dashboardUrl === url('internal/radar'),
    );
});

/**
 * @param  list<array<string, mixed>>|null  $vulnerabilities
 * @param  list<array<string, mixed>>  $extraVulnerabilities
 */
function vulnerableScan(?array $vulnerabilities = null, array $extraVulnerabilities = []): RadarScan
{
    $vulnerabilities = [
        ...($vulnerabilities ?? [
            vulnerabilityRecord(id: 'vuln-1', advisoryId: 'CVE-2025-0001', severity: 'high'),
            vulnerabilityRecord(id: 'vuln-2', advisoryId: 'GHSA-abcd', severity: 'medium', ecosystem: 'npm', packageName: 'pkg'),
        ]),
        ...$extraVulnerabilities,
    ];

    test()->travel(1)->seconds();

    return RadarScan::create([
        'score' => 80,
        'vulnerability_count' => count($vulnerabilities),
        'package_count' => 10,
        'payload' => [
            'packages' => [],
            'vulnerabilities' => $vulnerabilities,
            'outdated' => [],
            'abandoned' => [],
        ],
    ]);
}

/** @return array<string, mixed> */
function vulnerabilityRecord(
    string $id,
    string $advisoryId,
    string $severity,
    string $ecosystem = 'composer',
    string $packageName = 'foo/bar',
): array {
    return [
        'id' => $id,
        'ecosystem' => $ecosystem,
        'package_name' => $packageName,
        'installed_version' => '1.0.0',
        'severity' => $severity,
        'advisory_id' => $advisoryId,
        'is_direct' => true,
        'required_by' => [],
    ];
}
