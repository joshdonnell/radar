<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use JoshDonnell\Radar\Data\VulnerabilityNotificationData;

final class VulnerabilitiesFound extends Notification
{
    /**
     * @param  list<'mail'|'slack'>  $channels
     */
    public function __construct(
        public readonly VulnerabilityNotificationData $notification,
        public readonly array $channels,
    ) {}

    /** @return list<string> */
    public function via(): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->subject())
            ->view([
                'html' => 'radar::emails.vulnerabilities-found',
                'text' => 'radar::emails.vulnerabilities-found-text',
            ], $this->mailViewData());
    }

    public function toSlack(object $notifiable): SlackMessage
    {
        $counts = $this->notification->severityCounts();
        $total = count($this->notification->vulnerabilities);

        return (new SlackMessage())
            ->warning()
            ->content(sprintf(
                ':rotating_light: *%d vulnerabilit%s detected* in your project dependencies.',
                $total,
                $total === 1 ? 'y' : 'ies',
            ))
            ->attachment(function (SlackAttachment $attachment) use ($counts, $total): void {
                $attachment
                    ->title('Scan Details')
                    ->content(sprintf(
                        'Found %d vulnerabilit%s: %d critical, %d high, %d medium, %d low, %d unknown.',
                        $total,
                        $total === 1 ? 'y' : 'ies',
                        $counts['critical'],
                        $counts['high'],
                        $counts['medium'],
                        $counts['low'],
                        $counts['unknown'],
                    ));

                if ($this->notification->dashboardUrl !== null) {
                    $attachment->action('View in Radar', $this->notification->dashboardUrl);
                }
            });
    }

    /** @return array<string, mixed> */
    private function mailViewData(): array
    {
        $total = count($this->notification->vulnerabilities);

        return [
            'subject' => $this->subject(),
            'total' => $total,
            'pluralizedVulnerability' => $total === 1 ? 'vulnerability' : 'vulnerabilities',
            'counts' => $this->notification->severityCounts(),
            'vulnerabilities' => array_slice($this->notification->vulnerabilities, 0, 5),
            'remainingCount' => max(0, $total - 5),
            'dashboardUrl' => $this->notification->dashboardUrl,
        ];
    }

    private function subject(): string
    {
        $total = count($this->notification->vulnerabilities);

        return sprintf(
            '[Radar] %d vulnerabilit%s detected',
            $total,
            $total === 1 ? 'y' : 'ies',
        );
    }
}
