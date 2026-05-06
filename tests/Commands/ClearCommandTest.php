<?php

declare(strict_types=1);

use JoshDonnell\Radar\Models\RadarScan;

it('clears radar scan history', function (): void {
    RadarScan::factory()->count(3)->create();

    $this->artisan('radar:clear', ['--force' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('Cleared 3 Radar scan(s).');

    expect(RadarScan::query()->count())->toBe(0);
});

it('does not clear scan history without confirmation', function (): void {
    RadarScan::factory()->count(2)->create();

    $this->artisan('radar:clear')
        ->expectsConfirmation('Clear 2 Radar scan(s)?', 'no')
        ->assertSuccessful()
        ->expectsOutputToContain('Radar scan history was not cleared.');

    expect(RadarScan::query()->count())->toBe(2);
});

it('handles empty scan history', function (): void {
    $this->artisan('radar:clear')
        ->assertSuccessful()
        ->expectsOutputToContain('No Radar scans to clear.');
});
