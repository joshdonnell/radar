<?php

declare(strict_types=1);

it('can be created from radar scam data', function (): void {
    $finding = new JoshDonnell\Radar\Data\RadarScan(
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
