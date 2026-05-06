<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Tests;

use Illuminate\Filesystem\Filesystem;
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

        $this->copyBuiltAssetsToPublicPath();

        Schema::dropAllTables();

        $migration = include __DIR__.'/../database/migrations/create_radar_scans_table.php.stub';
        $runMigration = [$migration, 'up'];

        if (! is_callable($runMigration)) {
            throw new RuntimeException('Radar scan migration stub must return an executable migration.');
        }

        $runMigration();
    }

    private function copyBuiltAssetsToPublicPath(): void
    {
        $filesystem = new Filesystem();
        $sourcePath = __DIR__.'/../resources/dist';
        $targetPath = public_path('vendor/radar');

        $filesystem->deleteDirectory($targetPath);

        if (! $filesystem->isDirectory($sourcePath)) {
            return;
        }

        $filesystem->copyDirectory($sourcePath, $targetPath);
    }
}
