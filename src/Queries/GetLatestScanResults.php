<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Queries;

use Illuminate\Database\Eloquent\Builder;
use JoshDonnell\Radar\Models\RadarScan;
use JoshDonnell\Radar\Support\Config;

final class GetLatestScanResults
{
    /**
     * @return Builder<RadarScan>
     */
    public function builder(): Builder
    {
        $connection = Config::databaseConnection();
        $query = $connection ? RadarScan::on($connection) : RadarScan::query();

        return $query
            ->latest('created_at');
    }
}
