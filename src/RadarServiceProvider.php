<?php

declare(strict_types=1);

namespace JoshDonnell\Radar;

use Illuminate\Console\Scheduling\Schedule;
use JoshDonnell\Radar\Commands\ClearCommand;
use JoshDonnell\Radar\Commands\NotifyCommand;
use JoshDonnell\Radar\Commands\ScanCommand;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class RadarServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        if (config('radar.dashboard.enabled') !== null) {
            return;
        }

        $dashboardEnabled = $this->app->bound('env') ? ! $this->app->isProduction() : true;

        config()->set('radar.dashboard.enabled', $dashboardEnabled);
    }

    public function packageBooted(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $this->scheduleNotifications($schedule);
        });

        if ($this->app->resolved(Schedule::class)) {
            $this->scheduleNotifications($this->app->make(Schedule::class));
        }
    }

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

    private function scheduleNotifications(Schedule $schedule): void
    {
        if (config('radar.enabled') !== true) {
            return;
        }

        if (config('radar.notifications.schedule.enabled') !== true) {
            return;
        }

        $time = config('radar.notifications.schedule.time', '02:00');
        $timezone = config('radar.notifications.schedule.timezone');

        $event = $schedule
            ->command('radar:notify --scan')
            ->dailyAt(is_string($time) && $time !== '' ? $time : '02:00')
            ->withoutOverlapping();

        if (is_string($timezone) && $timezone !== '') {
            $event->timezone($timezone);
        }
    }
}
