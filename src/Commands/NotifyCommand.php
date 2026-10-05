<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use JoshDonnell\Radar\Actions\TrackNotifiedVulnerabilitiesAction;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Data\VulnerabilityNotificationData;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Notifications\VulnerabilitiesFound;
use JoshDonnell\Radar\Support\Config;

final class NotifyCommand extends Command
{
    public $signature = 'radar:notify {--scan : Run radar:scan before sending notifications}';

    public $description = 'Send notifications for new vulnerabilities found in the latest Radar scan';

    public function __construct(
        private readonly TrackNotifiedVulnerabilitiesAction $trackNotifiedVulnerabilities,
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

        $vulnerabilities = $this->eligibleVulnerabilities($scan);

        if ($vulnerabilities === []) {
            $this->trackNotifiedVulnerabilities->record(current: [], sent: []);

            $this->components->info('No vulnerabilities to notify about.');

            return self::SUCCESS;
        }

        if (! $this->hasNotificationChannel()) {
            $this->components->warn('No Radar notification channels are configured.');

            return self::SUCCESS;
        }

        $pendingVulnerabilities = $this->trackNotifiedVulnerabilities->pending($vulnerabilities);

        if ($pendingVulnerabilities === []) {
            $this->trackNotifiedVulnerabilities->record(current: $vulnerabilities, sent: []);

            $this->components->info('No new vulnerabilities to notify about.');

            return self::SUCCESS;
        }

        $mailRecipients = Config::notificationMailRecipients();
        $slackWebhookUrl = Config::notificationSlackWebhookUrl();

        $this->sendNotification(
            notification: new VulnerabilityNotificationData(
                scanId: $scan->id,
                vulnerabilities: $pendingVulnerabilities,
                dashboardUrl: $this->dashboardUrl(),
                scannedAt: $scan->created_at,
            ),
            mailRecipients: $mailRecipients,
            slackWebhookUrl: $slackWebhookUrl,
        );

        $this->trackNotifiedVulnerabilities->record(current: $vulnerabilities, sent: $pendingVulnerabilities);

        $this->components->info(sprintf(
            'Sent vulnerability notification for %d new finding(s) via %s.',
            count($pendingVulnerabilities),
            implode(', ', $this->targetedChannels($mailRecipients, $slackWebhookUrl)),
        ));

        return self::SUCCESS;
    }

    /** @return list<VulnerabilityFindingData> */
    private function eligibleVulnerabilities(RadarScan $scan): array
    {
        $minimumSeverity = Config::notificationMinimumSeverity();

        return array_values(array_filter(
            $scan->vulnerabilities(),
            static fn (VulnerabilityFindingData $vulnerability): bool => $vulnerability->severity->meetsNotificationThreshold($minimumSeverity),
        ));
    }

    /** @param list<string> $mailRecipients */
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

    private function dashboardUrl(): ?string
    {
        if (config('radar.dashboard.enabled') !== true) {
            return null;
        }

        return url(Config::path());
    }

    private function hasNotificationChannel(): bool
    {
        if (Config::notificationMailRecipients() !== []) {
            return true;
        }

        return Config::notificationSlackWebhookUrl() !== null;
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
}
