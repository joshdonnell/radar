<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

it('registers the dashboard outside production', function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);

    $this->get('/radar')
        ->assertOk()
        ->assertSee('id="radar"', false);
});

it('requires the configured gate outside local environments', function (): void {
    $this->get('/radar')
        ->assertForbidden();
});

it('allows dashboard access when the configured gate passes', function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);

    $this->get('/radar')
        ->assertOk()
        ->assertSee('id="radar"', false);
});
