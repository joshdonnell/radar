<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\CreateScanResultAction;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Queries\GetLatestScanResults;

it('queries the latest scan result', function (): void {
    RadarScan::factory()->create([
        'created_at' => now()->subMinutes(5),
        'payload' => ['name' => 'old'],
    ]);

    $latest = RadarScan::factory()->create([
        'created_at' => now(),
        'payload' => ['name' => 'latest'],
    ]);

    expect(app(GetLatestScanResults::class)->builder()->first()?->is($latest))->toBeTrue();
});

it('queries scan results when no dedicated database connection is configured', function (): void {
    $latest = app(CreateScanResultAction::class)->execute(payload: []);

    expect(app(GetLatestScanResults::class)->builder()->first()?->is($latest))->toBeTrue();
});
