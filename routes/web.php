<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JoshDonnell\Radar\Http\Controllers\DashboardController;
use JoshDonnell\Radar\Http\Controllers\LatestScanApiController;
use JoshDonnell\Radar\Http\Controllers\RunScanApiController;
use JoshDonnell\Radar\Support\Config;

if (! config('radar.enabled') || ! config('radar.dashboard.enabled')) {
    return;
}

Route::middleware(Config::routeMiddleware())
    ->prefix(Config::path())
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('radar.dashboard');
        Route::get('/api/scans/latest', LatestScanApiController::class)->name('radar.api.scans.latest');
        Route::post('/api/scans', RunScanApiController::class)->name('radar.api.scans.run');
    });
