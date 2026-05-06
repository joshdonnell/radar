<?php

declare(strict_types=1);

namespace JoshDonnell\Radar;

use JoshDonnell\Radar\Commands\ClearCommand;
use JoshDonnell\Radar\Commands\NotifyCommand;
use JoshDonnell\Radar\Commands\ScanCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class RadarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('radar')
            ->hasConfigFile()
            ->hasCommands([
                ClearCommand::class,
                NotifyCommand::class,
                ScanCommand::class,
            ])
            ->hasViews()
            ->hasAssets()
            ->hasRoute('web')
            ->hasMigration('create_radar_scans_table')
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->publishAssets();
            });
    }
}
