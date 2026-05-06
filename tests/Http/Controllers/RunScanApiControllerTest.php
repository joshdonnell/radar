<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use JoshDonnell\Radar\Models\RadarScan;

beforeEach(function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);
});

it('runs a scan through the api', function (): void {
    $response = $this->postJson('/radar/api/scans', [
        'path' => '/tmp/should-be-ignored',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('scan.package_count', fn (int $packageCount): bool => $packageCount >= 0)
        ->assertJsonPath('scan.vulnerability_count', fn (int $vulnerabilityCount): bool => $vulnerabilityCount >= 0)
        ->assertJsonStructure([
            'scan' => [
                'id',
                'score',
                'package_count',
                'vulnerability_count',
                'packages',
                'vulnerabilities',
                'outdated',
                'abandoned',
                'created_at',
            ],
        ]);

    expect(RadarScan::query()->count())->toBe(1);
});
