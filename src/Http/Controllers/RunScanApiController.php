<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JoshDonnell\Radar\Jobs\RunScanJob;
use JoshDonnell\Radar\Support\Config;
use JoshDonnell\Radar\Support\ScanStatus;
use Symfony\Component\HttpFoundation\Response;

final readonly class RunScanApiController
{
    public function __invoke(ScanStatus $scanStatus): JsonResponse
    {
        if (! $scanStatus->isPending()) {
            $scanStatus->markQueued();

            RunScanJob::dispatch(base_path())
                ->onConnection(Config::queueConnection())
                ->onQueue(Config::queueName());
        }

        return response()->json(
            ['status' => $scanStatus->toArray()],
            Response::HTTP_ACCEPTED,
        );
    }
}
