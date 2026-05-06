<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Data\RadarScanData;
use JoshDonnell\Radar\Queries\GetLatestScanResults;

final class LatestScanApiController
{
    public function __invoke(GetLatestScanResults $scanResults): JsonResponse
    {
        $scan = $scanResults->builder()->first();

        return response()->json([
            'scan' => $scan ? RadarScanData::fromModel($scan)->toArray() : null,
        ]);
    }
}
