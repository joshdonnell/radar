<?php

declare(strict_types=1);

namespace JoshDonnell\Radar;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use JoshDonnell\Radar\Commands\RadarCommand;

final class RadarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('radar')
            ->hasConfigFile()
            ->hasCommand(RadarCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Radar::class, fn (): Radar => new Radar);
    }
}
