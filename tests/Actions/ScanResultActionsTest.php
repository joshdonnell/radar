<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\CreateScanResultAction;
use JoshDonnell\Radar\Models\RadarScan;

it('stores scan results', function (): void {
    $scan = app(CreateScanResultAction::class)->execute(
        payload: [
            'packages' => [
                ['name' => 'laravel/framework'],
            ],
        ],
        score: 88,
        vulnerabilityCount: 1,
        packageCount: 12,
    );

    expect($scan)
        ->toBeInstanceOf(RadarScan::class)
        ->score->toBe(88)
        ->vulnerability_count->toBe(1)
        ->package_count->toBe(12)
        ->payload->toBe([
            'packages' => [
                ['name' => 'laravel/framework'],
            ],
        ])
        ->and(RadarScan::query()->count())->toBe(1);
});

it('stores scan results when no dedicated database connection is configured', function (): void {
    app(CreateScanResultAction::class)->execute(
        payload: [],
        score: 92,
        vulnerabilityCount: 0,
        packageCount: 18,
    );

    expect(RadarScan::query()->where('score', 92)->count())->toBe(1);
});

it('prunes scan results using numeric string prune days', function (): void {
    config(['radar.prune.days' => '7']);

    $staleScan = RadarScan::factory()->create([
        'created_at' => now()->subDays(8),
    ]);

    $freshScan = RadarScan::factory()->create([
        'created_at' => now()->subDays(6),
    ]);

    (new RadarScan())->pruneAll();

    expect(RadarScan::query()->whereKey($staleScan->id)->exists())->toBeFalse()
        ->and(RadarScan::query()->whereKey($freshScan->id)->exists())->toBeTrue();
});
