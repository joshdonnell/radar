<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Tests;

use Illuminate\Support\Facades\Schema;
use JoshDonnell\Radar\Http\Middleware\Authorize;
use Orchestra\Testbench\TestCase as Orchestra;
use RuntimeException;

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
        $app->make('config')->set('cache.default', 'array');

        Schema::dropAllTables();

        $migration = include __DIR__.'/../database/migrations/create_radar_scans_table.php.stub';
        $runMigration = [$migration, 'up'];

        if (! is_callable($runMigration)) {
            throw new RuntimeException('Radar scan migration stub must return an executable migration.');
        }

        $runMigration();
    }
}
