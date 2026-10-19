<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

it('respects the configured dashboard path', function (): void {
    config()->set('radar.path', 'dependency-radar');

    Route::setRoutes(new RouteCollection());

    require __DIR__.'/../../../routes/web.php';

    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);

    $this->get('/dependency-radar')
        ->assertOk()
        ->assertSee('id="radar"', false)
        ->assertSee('src="/dependency-radar/assets/', false);
});

it('serves assets under the configured dashboard path', function (): void {
    config()->set('radar.path', 'dependency-radar');

    require __DIR__.'/../../../routes/web.php';

    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);

    $manifest = json_decode((string) file_get_contents(__DIR__.'/../../../resources/dist/manifest.json'), true);

    $this->get('/dependency-radar/assets/'.basename($manifest['resources/js/app.ts']['file']))
        ->assertOk();
});
