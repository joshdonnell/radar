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
        $counts = $this->notification->severityCounts();
        $total = count($this->notification->vulnerabilities);

        $message = (new MailMessage())
            ->subject(sprintf(
                '[Radar] %d vulnerabilit%s detected',
                $total,
                $total === 1 ? 'y' : 'ies',
            ))
            ->line(sprintf(
                'Radar detected **%d vulnerabilit%s** in your project dependencies during scan `%s`.',
                $total,
                $total === 1 ? 'y' : 'ies',
                $this->notification->scanId,
            ))
            ->line(sprintf(
                'Breakdown: %d critical, %d high, %d medium, %d low, %d unknown.',
                $counts['critical'],
                $counts['high'],
                $counts['medium'],
                $counts['low'],
                $counts['unknown'],
            ));

        if ($this->notification->dashboardUrl !== null) {
            $message->action('View in Radar', $this->notification->dashboardUrl);
        }

        return $message;
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
                        'Scan `%s` found %d vulnerabilit%s: %d critical, %d high, %d medium, %d low, %d unknown.',
                        $this->notification->scanId,
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
}
