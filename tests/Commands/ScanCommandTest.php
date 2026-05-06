<?php

declare(strict_types=1);

use JoshDonnell\Radar\Models\RadarScan;

it('stores a radar scan snapshot', function (): void {
    $this->artisan('radar:scan', [
        '--path' => __DIR__.'/../Fixtures',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Stored Radar scan');

    $scan = RadarScan::query()->firstOrFail();

    expect($scan)
        ->score->toBe(43)
        ->vulnerability_count->toBe(4)
        ->package_count->toBe(12)
        ->and($scan->payload['packages'])->toHaveCount(12)
        ->and($scan->payload['vulnerabilities'])->toHaveCount(4)
        ->and($scan->payload['outdated'])->toHaveCount(4)
        ->and($scan->payload['abandoned'])->toHaveCount(2);
});

it('handles projects without dependency files', function (): void {
    $basepath = sys_get_temp_dir().'/radar-empty-project-'.bin2hex(random_bytes(4));

    mkdir($basepath);

    try {
        $this->artisan('radar:scan', [
            '--path' => $basepath,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('Stored Radar scan');
    } finally {
        rmdir($basepath);
    }

    $scan = RadarScan::query()->firstOrFail();

    expect($scan)
        ->score->toBe(100)
        ->vulnerability_count->toBe(0)
        ->package_count->toBe(0)
        ->and($scan->payload)->toBe([
            'packages' => [],
            'vulnerabilities' => [],
            'outdated' => [],
            'abandoned' => [],
        ]);
});

it('fails clearly when the scan path does not exist', function (): void {
    $basepath = sys_get_temp_dir().'/radar-missing-project-'.bin2hex(random_bytes(4));

    $this->artisan('radar:scan', [
        '--path' => $basepath,
    ])
        ->assertFailed()
        ->expectsOutputToContain(sprintf('The path [%s] does not exist.', $basepath));

    expect(RadarScan::query()->count())->toBe(0);
});
