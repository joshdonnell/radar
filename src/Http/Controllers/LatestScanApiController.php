<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Data\RadarScan;
use JoshDonnell\Radar\Queries\GetLatestScanResults;

final class LatestScanApiController
{
    public function __invoke(GetLatestScanResults $scanResults): JsonResponse
    {
        $scan = $scanResults->builder()->first();

        return response()->json([
            'scan' => $scan ? (new RadarScan(
                id: $scan->id,
                score: $scan->score,
                package_count: $scan->package_count,
                vulnerability_count: $scan->vulnerability_count,
                payload: $scan->payload,
                created_at: $scan->created_at,
            ))->toArray() : null,
        ]);
    }
}
