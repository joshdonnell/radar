<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Data\RadarScanData;
use JoshDonnell\Radar\Queries\GetLatestScanResults;
use JoshDonnell\Radar\Support\ScanStatus;

final class LatestScanApiController
{
    public function __invoke(GetLatestScanResults $scanResults, ScanStatus $scanStatus): JsonResponse
    {
        $scan = $scanResults->builder()->first();

        return response()->json([
            'scan' => $scan ? RadarScanData::fromModel($scan)->toArray() : null,
            'status' => $scanStatus->toArray(),
        ]);
    }
}
