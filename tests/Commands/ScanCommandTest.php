<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
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

it('emits generic CI output and fails when the severity threshold is met', function (): void {
    $exitCode = Artisan::call('radar:scan', [
        '--path' => __DIR__.'/../Fixtures',
        '--ci' => true,
        '--severity' => 'high',
    ]);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())
        ->toContain('Radar scan completed with 4 vulnerability finding(s).')
        ->toContain('CI severity threshold: high. Failing vulnerability finding(s): 2.')
        ->toContain('[ERROR] laravel/framework high severity vulnerability found. CVE: CVE-2026-1001')
        ->toContain('[WARNING] symfony/console medium severity vulnerability found')
        ->not->toContain('outdated package finding(s)')
        ->not->toContain('abandoned package finding(s)');

    expect(RadarScan::query()->count())->toBe(1);
});

it('passes CI mode when vulnerabilities are below the severity threshold', function (): void {
    $exitCode = Artisan::call('radar:scan', [
        '--path' => __DIR__.'/../Fixtures',
        '--ci' => true,
        '--severity' => 'critical',
    ]);

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())
        ->toContain('CI severity threshold: critical. Failing vulnerability finding(s): 0.')
        ->toContain('Radar scan passed. No vulnerabilities at critical severity or above.');
});

it('validates CI severity options before scanning', function (): void {
    $exitCode = Artisan::call('radar:scan', [
        '--path' => __DIR__.'/../Fixtures',
        '--ci' => true,
        '--severity' => 'unknown',
    ]);

    expect($exitCode)->toBe(2)
        ->and(Artisan::output())->toContain('Unsupported CI severity threshold')
        ->and(RadarScan::query()->count())->toBe(0);
});
