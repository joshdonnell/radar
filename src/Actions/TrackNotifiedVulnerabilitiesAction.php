<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use Illuminate\Support\Facades\Cache;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;

/**
 * Remembers which vulnerabilities have already been notified about, so each
 * finding is announced once rather than every time a scan still contains it.
 */
final readonly class TrackNotifiedVulnerabilitiesAction
{
    private const string CACHE_KEY = 'radar:notifications:notified';

    /**
     * @param  list<VulnerabilityFindingData>  $vulnerabilities
     * @return list<VulnerabilityFindingData>
     */
    public function pending(array $vulnerabilities): array
    {
        $notified = $this->notified();
        $remindBefore = $this->remindBefore();

        return array_values(array_filter(
            $vulnerabilities,
            static function (VulnerabilityFindingData $vulnerability) use ($notified, $remindBefore): bool {
                $notifiedAt = $notified[$vulnerability->fingerprint()] ?? null;

                if ($notifiedAt === null) {
                    return true;
                }

                return $remindBefore !== null && $notifiedAt <= $remindBefore;
            },
        ));
    }

    /**
     * Resolved findings are forgotten, so a vulnerability that comes back
     * later is announced again.
     *
     * @param  list<VulnerabilityFindingData>  $current
     * @param  list<VulnerabilityFindingData>  $sent
     */
    public function record(array $current, array $sent): void
    {
        $previous = $this->notified();
        $sentFingerprints = array_map(static fn (VulnerabilityFindingData $vulnerability): string => $vulnerability->fingerprint(), $sent);
        $now = now()->getTimestamp();
        $notified = [];

        foreach ($current as $vulnerability) {
            $fingerprint = $vulnerability->fingerprint();

            if (in_array($fingerprint, $sentFingerprints, true)) {
                $notified[$fingerprint] = $now;

                continue;
            }

            if (isset($previous[$fingerprint])) {
                $notified[$fingerprint] = $previous[$fingerprint];
            }
        }

        Cache::forever(self::CACHE_KEY, $notified);
    }

    /** @return array<string, int> */
    private function notified(): array
    {
        $notified = Cache::get(self::CACHE_KEY, []);

        if (! is_array($notified)) {
            return [];
        }

        return array_filter(
            $notified,
            static fn (mixed $timestamp, mixed $fingerprint): bool => is_string($fingerprint) && is_int($timestamp),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function remindBefore(): ?int
    {
        $configuredInterval = config('radar.notifications.remind_after');
        $interval = false;

        if (is_int($configuredInterval) || is_string($configuredInterval)) {
            $interval = filter_var($configuredInterval, FILTER_VALIDATE_INT);
        }

        if (! is_int($interval) || $interval <= 0) {
            return null;
        }

        return now()->getTimestamp() - $interval;
    }
}
