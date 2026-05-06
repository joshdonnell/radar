<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Actions\RunScanAction;
use JoshDonnell\Radar\Data\RadarScan;

final readonly class RunScanApiController
{
    public function __construct(
        private RunScanAction $runScan,
    ) {}

    public function __invoke(): JsonResponse
    {
        $scan = $this->runScan->execute(
            basepath: base_path(),
        );

        return response()->json([
            'scan' => (new RadarScan(
                id: $scan->id,
                score: $scan->score,
                package_count: $scan->package_count,
                vulnerability_count: $scan->vulnerability_count,
                payload: $scan->payload,
                created_at: $scan->created_at,
            ))->toArray(),
        ]);
    }
}
