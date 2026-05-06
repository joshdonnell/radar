<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use JoshDonnell\Radar\Models\RadarScan;

beforeEach(function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);
});

it('returns the latest scan via api', function (): void {
    RadarScan::factory()->create([
        'score' => 96,
        'package_count' => 24,
        'vulnerability_count' => 1,
        'payload' => [
            'packages' => [
                ['name' => 'laravel/framework'],
            ],
            'vulnerabilities' => [
                ['package_name' => 'laravel/framework'],
            ],
            'outdated' => [
                ['package_name' => 'spatie/laravel-package-tools'],
            ],
            'abandoned' => [
                ['package_name' => 'swiftmailer/swiftmailer'],
            ],
        ],
        'created_at' => now(),
    ]);

    $response = $this->getJson('/radar/api/scans/latest');

    $response
        ->assertOk()
        ->assertJsonPath('scan.score', 96)
        ->assertJsonPath('scan.package_count', 24)
        ->assertJsonPath('scan.vulnerability_count', 1)
        ->assertJsonPath('scan.packages.0.name', 'laravel/framework')
        ->assertJsonPath('scan.vulnerabilities.0.package_name', 'laravel/framework')
        ->assertJsonPath('scan.outdated.0.package_name', 'spatie/laravel-package-tools')
        ->assertJsonPath('scan.abandoned.0.package_name', 'swiftmailer/swiftmailer');
});

it('returns null scan when no scans exist', function (): void {
    $this->getJson('/radar/api/scans/latest')
        ->assertOk()
        ->assertJsonPath('scan', null);
});
