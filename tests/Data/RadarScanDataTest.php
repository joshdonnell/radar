<?php

declare(strict_types=1);

use JoshDonnell\Radar\Data\RadarScanData;
use JoshDonnell\Radar\Models\RadarScan;

it('can be created from radar scan data', function (): void {
    $finding = new RadarScanData(
        id: '4fd47b79-7fa1-4615-bfaa-28a3f8d3fdbe',
        score: 50,
        package_count: 100,
        vulnerability_count: 10,
        payload: [],
        created_at: null,
    );

    expect($finding->toArray())->toBe([
        'id' => '4fd47b79-7fa1-4615-bfaa-28a3f8d3fdbe',
        'score' => 50,
        'package_count' => 100,
        'vulnerability_count' => 10,
        'packages' => [],
        'vulnerabilities' => [],
        'outdated' => [],
        'abandoned' => [],
        'created_at' => null,
        'created_at_human' => null,
    ]);
});

it('can be created from a RadarScan model', function (): void {
    $model = RadarScan::factory()->create();

    $data = RadarScanData::fromModel($model);

    expect($data)->toBeInstanceOf(RadarScanData::class)
        ->and($data->toArray())->toBe([
            'id' => $model->id,
            'score' => $model->score,
            'package_count' => $model->package_count,
            'vulnerability_count' => $model->vulnerability_count,
            'packages' => $model->payload['packages'] ?? [],
            'vulnerabilities' => $model->payload['vulnerabilities'] ?? [],
            'outdated' => $model->payload['outdated'] ?? [],
            'abandoned' => $model->payload['abandoned'] ?? [],
            'created_at' => $model->created_at?->toIso8601String(),
            'created_at_human' => $model->created_at?->diffForHumans(),
        ]);
});
