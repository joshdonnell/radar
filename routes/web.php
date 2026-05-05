<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JoshDonnell\Radar\Http\Controllers\DashboardController;
use JoshDonnell\Radar\Http\Controllers\ScanApiController;
use JoshDonnell\Radar\Support\Config;

if (! config('radar.enabled') || app()->environment('production')) {
    return;
}

Route::middleware(Config::routeMiddleware())
    ->prefix(Config::path())
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('radar.dashboard');
        Route::get('/api/scans/latest', [ScanApiController::class, 'latest'])->name('radar.api.scans.latest');
    });
