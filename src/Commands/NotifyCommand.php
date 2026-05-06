<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use JoshDonnell\Radar\Actions\ShouldSendVulnerabilityNotificationAction;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Data\VulnerabilityNotificationData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Notifications\VulnerabilitiesFound;

final class NotifyCommand extends Command
{
    use ReadsJsonFiles;

    public $signature = 'radar:notify {--scan : Run radar:scan before sending notifications}';

    public $description = 'Send notifications for vulnerabilities found in the latest Radar scan';

    public function __construct(
        private readonly ShouldSendVulnerabilityNotificationAction $shouldSendVulnerabilityNotification,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('scan') === true) {
            $exitCode = $this->call('radar:scan');

            if ($exitCode !== self::SUCCESS) {
                return $exitCode;
            }
        }

        $scan = RadarScan::query()->latest('created_at')->first();

        if (! $scan instanceof RadarScan) {
            $this->components->info('No Radar scans to notify about.');

            return self::SUCCESS;
        }

        $vulnerabilities = $this->vulnerabilities($scan);

        if ($vulnerabilities === []) {
            $this->components->info('No vulnerabilities to notify about.');

            return self::SUCCESS;
        }

        if (! $this->hasNotificationChannel()) {
            $this->components->warn('No Radar notification channels are configured.');

            return self::SUCCESS;
        }

        $path = config('radar.path', 'radar');

        $notification = new VulnerabilityNotificationData(
            scanId: (string) $scan->id,
            vulnerabilities: $vulnerabilities,
            dashboardUrl: url(is_string($path) ? $path : 'radar'),
        );

        if (! $this->shouldSendVulnerabilityNotification->execute($notification)) {
            $this->components->info('Vulnerability notification already sent for this finding set.');

            return self::SUCCESS;
        }

        $mailRecipients = $this->mailRecipients();
        $slackWebhookUrl = $this->slackWebhookUrl();

        $this->sendNotification($notification, $mailRecipients, $slackWebhookUrl);
        $this->shouldSendVulnerabilityNotification->markAsSent($notification);

        $this->components->info(sprintf(
            'Sent vulnerability notification for %d finding(s) via %s.',
            count($vulnerabilities),
            implode(', ', $this->targetedChannels($mailRecipients, $slackWebhookUrl)),
        ));

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $mailRecipients
     */
    private function sendNotification(
        VulnerabilityNotificationData $notification,
        array $mailRecipients,
        ?string $slackWebhookUrl,
    ): void {
        $notifiable = Notification::route('mail', $mailRecipients);

        if ($slackWebhookUrl !== null) {
            $notifiable->route('slack', $slackWebhookUrl);
        }

        $notifiable->notify(new VulnerabilitiesFound(
            notification: $notification,
            channels: $this->targetedChannels($mailRecipients, $slackWebhookUrl),
        ));
    }

    /** @return list<VulnerabilityFindingData> */
    private function vulnerabilities(RadarScan $scan): array
    {
        $vulnerabilities = $scan->payload['vulnerabilities'] ?? [];

        if (! is_array($vulnerabilities)) {
            return [];
        }

        $findings = [];

        foreach ($vulnerabilities as $vulnerability) {
            if (! is_array($vulnerability)) {
                continue;
            }

            /** @var array<string, mixed> $vulnerability */
            $findings[] = $this->vulnerabilityFinding($vulnerability);
        }

        return $findings;
    }

    /** @param array<string, mixed> $vulnerability */
    private function vulnerabilityFinding(array $vulnerability): VulnerabilityFindingData
    {
        return new VulnerabilityFindingData(
            id: $this->stringValue($vulnerability, 'id') ?? 'unknown-advisory',
            ecosystem: Ecosystem::tryFrom($this->stringValue($vulnerability, 'ecosystem') ?? '') ?? Ecosystem::Composer,
            packageName: $this->stringValue($vulnerability, 'package_name') ?? 'unknown/package',
            installedVersion: $this->stringValue($vulnerability, 'installed_version') ?? 'unknown',
            severity: VulnerabilitySeverity::tryFrom($this->stringValue($vulnerability, 'severity') ?? '') ?? VulnerabilitySeverity::Unknown,
            advisoryId: $this->stringValue($vulnerability, 'advisory_id') ?? 'unknown-advisory',
            isDirect: ($vulnerability['is_direct'] ?? false) === true,
            cve: $this->stringValue($vulnerability, 'cve'),
            affectedVersions: $this->stringValue($vulnerability, 'affected_versions'),
            patchedVersion: $this->stringValue($vulnerability, 'patched_version'),
            advisoryUrl: $this->stringValue($vulnerability, 'advisory_url'),
            recommendation: $this->stringValue($vulnerability, 'recommendation'),
        );
    }

    private function hasNotificationChannel(): bool
    {
        if ($this->mailRecipients() !== []) {
            return true;
        }

        return $this->slackWebhookUrl() !== null;
    }

    /**
     * @param  list<string>  $mailRecipients
     * @return list<'mail'|'slack'>
     */
    private function targetedChannels(array $mailRecipients, ?string $slackWebhookUrl): array
    {
        $channels = [];

        if ($mailRecipients !== []) {
            $channels[] = 'mail';
        }

        if ($slackWebhookUrl !== null) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    /** @return list<string> */
    private function mailRecipients(): array
    {
        $recipients = config('radar.notifications.routes.mail', []);

        if (! is_array($recipients)) {
            return [];
        }

        return array_values(array_filter($recipients, is_string(...)));
    }

    private function slackWebhookUrl(): ?string
    {
        $webhookUrl = config('radar.notifications.routes.slack');

        return is_string($webhookUrl) && $webhookUrl !== '' ? $webhookUrl : null;
    }
}
