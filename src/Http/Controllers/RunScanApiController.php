<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Actions\RunScanAction;
use JoshDonnell\Radar\Http\Resources\RadarScanResource;

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
            'scan' => new RadarScanResource($scan),
        ]);
    }
}
