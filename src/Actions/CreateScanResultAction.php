<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Models\RadarScan;

final readonly class CreateScanResultAction
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function execute(
        array $payload,
        ?int $score = null,
        int $vulnerabilityCount = 0,
        int $packageCount = 0,
    ): RadarScan {
        return RadarScan::query()->create([
            'score' => $score,
            'vulnerability_count' => $vulnerabilityCount,
            'package_count' => $packageCount,
            'payload' => $payload,
        ]);
    }
}
