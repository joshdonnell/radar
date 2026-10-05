<?php

declare(strict_types=1);

use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Support\CiScanReport;

it('reports an incomplete scan as invalid when an audit could not run', function (): void {
    $scan = RadarScan::factory()->create([
        'payload' => [
            'vulnerabilities' => [],
            'warnings' => [
                ['ecosystem' => 'composer', 'check' => 'vulnerabilities', 'message' => '`composer audit --format=json` timed out after 60s.'],
            ],
        ],
    ]);

    $report = new CiScanReport($scan, VulnerabilitySeverity::Low);

    expect($report->exitCode())->toBe(2)
        ->and($report->lines())
        ->toContain('[INCOMPLETE] composer audit: `composer audit --format=json` timed out after 60s.')
        ->toContain('Radar scan incomplete. One or more dependency audits could not run.');
});

it('fails rather than reporting incomplete when a vulnerability meets the threshold', function (): void {
    $scan = RadarScan::factory()->create([
        'payload' => [
            'vulnerabilities' => [
                ['package_name' => 'laravel/framework', 'severity' => 'high'],
            ],
            'warnings' => [
                ['ecosystem' => 'npm', 'check' => 'vulnerabilities', 'message' => 'Radar cannot audit Bun projects yet.'],
            ],
        ],
    ]);

    expect((new CiScanReport($scan, VulnerabilitySeverity::High))->exitCode())->toBe(1);
});

it('ignores warnings from checks other than vulnerability audits', function (): void {
    $scan = RadarScan::factory()->create([
        'payload' => [
            'warnings' => [
                ['ecosystem' => 'npm', 'check' => 'outdated', 'message' => 'Radar cannot check Bun projects for outdated packages yet.'],
            ],
        ],
    ]);

    $report = new CiScanReport($scan, VulnerabilitySeverity::Low);

    expect($report->exitCode())->toBe(0)
        ->and($report->lines())->toContain('Radar scan passed. No vulnerabilities found.');
});
