<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

it('respects the configured dashboard path', function (): void {
    config()->set('radar.path', 'dependency-radar');

    require __DIR__.'/../../../routes/web.php';

    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);

    $this->get('/dependency-radar')
        ->assertOk()
        ->assertSee('id="radar"', false);
});
