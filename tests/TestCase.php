<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Tests;

use Illuminate\Support\Facades\Schema;
use JoshDonnell\Radar\Http\Middleware\Authorize;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            \JoshDonnell\Radar\RadarServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->make('config')->set('radar.middleware', [Authorize::class]);

        Schema::dropAllTables();

        $migration = include __DIR__.'/../database/migrations/create_radar_scans_table.php.stub';
        $migration->up();
    }
}
