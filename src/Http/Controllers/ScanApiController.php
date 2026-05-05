<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Http\Resources\RadarScanResource;
use JoshDonnell\Radar\Queries\GetLatestScanResults;

final class ScanApiController
{
    public function latest(GetLatestScanResults $scanResults): JsonResponse
    {
        $scan = $scanResults->builder()->first();

        return response()->json([
            'scan' => $scan ? new RadarScanResource($scan) : null,
        ]);
    }
}
