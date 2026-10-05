<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Http\Middleware\Authorize;

final class Config
{
    public static function authorizationGate(): string
    {
        $gate = config('radar.authorization.gate', 'viewRadar');

        return is_string($gate) ? $gate : 'viewRadar';
    }

    public static function path(): string
    {
        $path = config('radar.path', 'radar');

        if (! is_string($path)) {
            return 'radar';
        }

        return mb_trim($path, '/');
    }

    public static function outdatedPenaltyCap(): int
    {
        return self::penaltyCap('radar.scoring.outdated_penalty_cap', 30);
    }

    public static function abandonedPenaltyCap(): int
    {
        return self::penaltyCap('radar.scoring.abandoned_penalty_cap', 30);
    }

    /**
     * @return array<int, string>
     */
    public static function routeMiddleware(): array
    {
        $middleware = config('radar.middleware', ['web']);

        if (! is_array($middleware)) {
            return ['web', Authorize::class];
        }

        $middleware = array_values(array_filter($middleware, is_string(...)));

        if (! in_array(Authorize::class, $middleware, true)) {
            $middleware[] = Authorize::class;
        }

        return $middleware;
    }

    /** @return list<string> */
    public static function notificationMailRecipients(): array
    {
        $recipients = config('radar.notifications.routes.mail', []);

        if (is_string($recipients)) {
            $recipients = explode(',', $recipients);
        }

        if (! is_array($recipients)) {
            return [];
        }

        $recipients = array_map(
            static fn (mixed $recipient): string => is_string($recipient) ? mb_trim($recipient) : '',
            $recipients,
        );

        return array_values(array_filter($recipients, static fn (string $recipient): bool => $recipient !== ''));
    }

    public static function notificationSlackWebhookUrl(): ?string
    {
        $webhookUrl = config('radar.notifications.routes.slack');

        return is_string($webhookUrl) && $webhookUrl !== '' ? $webhookUrl : null;
    }

    public static function notificationMinimumSeverity(): VulnerabilitySeverity
    {
        $severity = config('radar.notifications.min_severity', VulnerabilitySeverity::Low->value);

        return VulnerabilitySeverity::fromThreshold(is_string($severity) ? $severity : null)
            ?? VulnerabilitySeverity::Low;
    }

    public static function queueConnection(): ?string
    {
        $connection = config('radar.queue.connection');

        return is_string($connection) && $connection !== '' ? $connection : null;
    }

    public static function queueName(): ?string
    {
        $queue = config('radar.queue.name');

        return is_string($queue) && $queue !== '' ? $queue : null;
    }

    private static function penaltyCap(string $key, int $default): int
    {
        $value = config($key, $default);

        if (is_int($value) || is_string($value)) {
            $cap = filter_var($value, FILTER_VALIDATE_INT);

            if (is_int($cap) && $cap >= 0) {
                return $cap;
            }
        }

        return $default;
    }
}
