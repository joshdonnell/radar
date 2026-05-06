<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Queries;

use Illuminate\Database\Eloquent\Builder;
use JoshDonnell\Radar\Models\RadarScan;

final class GetLatestScanResults
{
    /**
     * @return Builder<RadarScan>
     */
    public function builder(): Builder
    {
        return RadarScan::query()
            ->latest('created_at');
    }
}
