<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use JoshDonnell\Radar\Jobs\RunScanJob;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Support\ScanStatus;

beforeEach(function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);
});

it('queues a scan of the application base path', function (): void {
    Queue::fake();

    $this->postJson('/radar/api/scans', ['path' => '/tmp/should-be-ignored'])
        ->assertAccepted()
        ->assertJsonPath('status.pending', true)
        ->assertJsonPath('status.error', null);

    Queue::assertPushed(RunScanJob::class, fn (RunScanJob $job): bool => $job->basepath === base_path());
});

it('does not queue another scan while one is pending', function (): void {
    Queue::fake();

    $this->postJson('/radar/api/scans')->assertAccepted();
    $this->postJson('/radar/api/scans')->assertAccepted()->assertJsonPath('status.pending', true);

    Queue::assertPushed(RunScanJob::class, 1);
});

it('runs the scan and clears the pending status on a sync queue', function (): void {
    config(['queue.default' => 'sync']);

    $this->postJson('/radar/api/scans')
        ->assertAccepted()
        ->assertJsonPath('status.pending', false);

    expect(RadarScan::query()->count())->toBe(1);
});

it('records the error when the scan job fails', function (): void {
    app(ScanStatus::class)->markQueued();

    (new RunScanJob(base_path()))->failed(new RuntimeException('composer exploded'));

    expect(app(ScanStatus::class)->toArray())->toMatchArray([
        'pending' => false,
        'error' => 'composer exploded',
    ]);
});

it('throttles scan requests', function (): void {
    Queue::fake();

    foreach (range(1, 10) as $attempt) {
        app(ScanStatus::class)->markFinished();

        $this->postJson('/radar/api/scans')->assertAccepted();
    }

    $this->postJson('/radar/api/scans')->assertTooManyRequests();
});
