<?php

declare(strict_types=1);

use JoshDonnell\Radar\Data\RadarScanData;
use JoshDonnell\Radar\Models\RadarScan;

it('serialises an empty scan', function (): void {
    $model = RadarScan::factory()->create([
        'score' => 50,
        'package_count' => 100,
        'vulnerability_count' => 10,
        'payload' => [],
    ]);

    expect(RadarScanData::fromModel($model)->toArray())->toBe([
        'id' => $model->id,
        'score' => 50,
        'package_count' => 100,
        'vulnerability_count' => 10,
        'packages' => [],
        'vulnerabilities' => [],
        'outdated' => [],
        'abandoned' => [],
        'warnings' => [],
        'created_at' => $model->created_at?->toIso8601String(),
    ]);
});

it('normalises stored findings through their data objects', function (): void {
    $model = RadarScan::factory()->create([
        'payload' => [
            'packages' => [
                ['name' => 'laravel/framework', 'ecosystem' => 'composer', 'installed_version' => '12.0.0', 'is_direct' => true],
                'not-a-record',
            ],
            'vulnerabilities' => [
                ['package_name' => 'laravel/framework', 'severity' => 'critical', 'title' => 'Remote code execution'],
            ],
            'outdated' => [
                ['package_name' => 'vite', 'ecosystem' => 'npm', 'update_type' => 'not-a-type'],
            ],
            'abandoned' => [
                ['package_name' => 'swiftmailer/swiftmailer'],
            ],
            'warnings' => [
                ['ecosystem' => 'npm', 'check' => 'outdated', 'message' => 'Radar cannot check Bun projects for outdated packages yet.'],
            ],
        ],
    ]);

    $data = RadarScanData::fromModel($model)->toArray();

    expect($data['packages'])->toHaveCount(1)
        ->and($data['packages'][0])->toMatchArray([
            'name' => 'laravel/framework',
            'is_direct' => true,
            'dependency_type' => 'production',
        ])
        ->and($data['vulnerabilities'][0])->toMatchArray([
            'severity' => 'critical',
            'title' => 'Remote code execution',
            'alternative_commands' => [],
        ])
        ->and($data['outdated'][0])->toMatchArray([
            'ecosystem' => 'npm',
            'update_type' => 'unknown',
        ])
        ->and($data['abandoned'][0]['package_name'])->toBe('swiftmailer/swiftmailer')
        ->and($data['warnings'])->toBe([
            ['ecosystem' => 'npm', 'check' => 'outdated', 'message' => 'Radar cannot check Bun projects for outdated packages yet.'],
        ]);
});
