<?php

declare(strict_types=1);

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Data\VulnerabilityNotificationData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Notifications\VulnerabilitiesFound;

it('sends via the configured notification channels', function (): void {
    Notification::fake();

    $notification = vulnerabilitiesFound(channels: ['mail', 'slack']);

    Notification::route('mail', 'security@example.com')
        ->route('slack', 'https://hooks.slack.com/services/example')
        ->notify($notification);

    Notification::assertSentOnDemand(VulnerabilitiesFound::class, function (
        VulnerabilitiesFound $notification,
        array $channels,
        AnonymousNotifiable $notifiable,
    ): bool {
        expect($channels)->toBe(['mail', 'slack'])
            ->and($notification->via())->toBe(['mail', 'slack'])
            ->and($notifiable->routeNotificationFor('mail'))->toBe('security@example.com')
            ->and($notifiable->routeNotificationFor('slack'))->toBe('https://hooks.slack.com/services/example');

        return true;
    });
});

it('builds a styled mail notification', function (): void {
    $mail = vulnerabilitiesFound(channels: ['mail'])->toMail(new AnonymousNotifiable());

    expect($mail)
        ->subject->toBe('[Radar] 2 vulnerabilities detected')
        ->view->toBe([
            'html' => 'radar::emails.vulnerabilities-found',
            'text' => 'radar::emails.vulnerabilities-found-text',
        ])
        ->and($mail->viewData)
        ->toMatchArray([
            'total' => 2,
            'pluralizedVulnerability' => 'vulnerabilities',
            'dashboardUrl' => 'https://example.com/radar',
        ])
        ->and($mail->viewData['counts'])->toBe([
            'critical' => 1,
            'high' => 1,
            'medium' => 0,
            'low' => 0,
            'unknown' => 0,
        ]);
});

it('omits the dashboard action when no dashboard url is available', function (): void {
    $mail = vulnerabilitiesFound(channels: ['mail'], dashboardUrl: null)->toMail(new AnonymousNotifiable());

    expect($mail->viewData['dashboardUrl'])->toBeNull();
});

it('builds a useful slack notification', function (): void {
    $slack = vulnerabilitiesFound(channels: ['slack'])->toSlack(new AnonymousNotifiable());
    $attachment = $slack->attachments[0] ?? null;

    expect($slack)
        ->level->toBe('warning')
        ->content->toContain('2 vulnerabilities detected')
        ->and($attachment)
        ->not->toBeNull()
        ->title->toBe('Scan Details')
        ->content->toContain('Found 2 vulnerabilities')
        ->content->not->toContain('scan-abc-123')
        ->actions->toHaveCount(1);
});

/** @param list<'mail'|'slack'> $channels */
function vulnerabilitiesFound(array $channels, ?string $dashboardUrl = 'https://example.com/radar'): VulnerabilitiesFound
{
    return new VulnerabilitiesFound(
        notification: new VulnerabilityNotificationData(
            scanId: 'scan-abc-123',
            vulnerabilities: [
                new VulnerabilityFindingData(
                    id: 'vuln-1',
                    ecosystem: Ecosystem::Composer,
                    packageName: 'foo/bar',
                    installedVersion: '1.2.3',
                    severity: VulnerabilitySeverity::High,
                    advisoryId: 'CVE-2025-0001',
                    isDirect: true,
                ),
                new VulnerabilityFindingData(
                    id: 'vuln-2',
                    ecosystem: Ecosystem::Npm,
                    packageName: 'some-package',
                    installedVersion: '4.5.6',
                    severity: VulnerabilitySeverity::Critical,
                    advisoryId: 'GHSA-abcd',
                    isDirect: false,
                ),
            ],
            dashboardUrl: $dashboardUrl,
        ),
        channels: $channels,
    );
}
