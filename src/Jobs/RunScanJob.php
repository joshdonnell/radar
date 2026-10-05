<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JoshDonnell\Radar\Actions\RunScanAction;
use JoshDonnell\Radar\Support\ScanStatus;
use Throwable;

final class RunScanJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 600;

    public int $uniqueFor = 900;

    public function __construct(
        public readonly string $basepath,
    ) {}

    public function handle(RunScanAction $runScan, ScanStatus $scanStatus): void
    {
        $runScan->execute($this->basepath);

        $scanStatus->markFinished();
    }

    public function failed(?Throwable $exception): void
    {
        app(ScanStatus::class)->markFailed($exception?->getMessage() ?? 'The scan job failed.');
    }
}
