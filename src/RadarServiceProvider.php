<?php

declare(strict_types=1);

namespace JoshDonnell\Radar;

use JoshDonnell\Radar\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class RadarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('radar')
            ->hasConfigFile()
            ->hasMigration('create_radar_scans_table')
            ->hasCommand(InstallCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Radar::class, fn (): Radar => new Radar);
    }
}
