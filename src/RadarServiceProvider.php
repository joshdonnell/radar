<?php

declare(strict_types=1);

namespace JoshDonnell\Radar;

use JoshDonnell\Radar\Commands\RadarCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
