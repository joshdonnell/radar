<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

/**
 * Tracks a dashboard-triggered scan while it waits for, and runs on, the
 * queue, so the dashboard can show progress and report failures.
 */
final class ScanStatus
{
    private const string QUEUED_KEY = 'radar:scan:queued_at';

    private const string ERROR_KEY = 'radar:scan:error';

    /**
     * How long a queued scan is considered pending before the dashboard stops
     * waiting for it, for example when no queue worker is running.
     */
    private const int PENDING_TTL = 900;

    public function isPending(): bool
    {
        return $this->queuedAt() instanceof CarbonImmutable;
    }

    public function markQueued(): void
    {
        Cache::forget(self::ERROR_KEY);
        Cache::put(self::QUEUED_KEY, now()->toIso8601String(), self::PENDING_TTL);
    }

    public function markFinished(): void
    {
        Cache::forget(self::QUEUED_KEY);
        Cache::forget(self::ERROR_KEY);
    }

    public function markFailed(string $error): void
    {
        Cache::forget(self::QUEUED_KEY);
        Cache::put(self::ERROR_KEY, mb_strimwidth($error, 0, 500, '...'), 3600);
    }

    /** @return array{pending: bool, queued_at: string|null, error: string|null} */
    public function toArray(): array
    {
        $error = Cache::get(self::ERROR_KEY);

        return [
            'pending' => $this->isPending(),
            'queued_at' => $this->queuedAt()?->toIso8601String(),
            'error' => is_string($error) ? $error : null,
        ];
    }

    private function queuedAt(): ?CarbonImmutable
    {
        $queuedAt = Cache::get(self::QUEUED_KEY);

        return is_string($queuedAt) ? CarbonImmutable::parse($queuedAt) : null;
    }
}
