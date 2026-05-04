<?php

declare(strict_types=1);

it('installs radar scaffolding', function (): void {
    $this->artisan('radar:install')
        ->assertSuccessful()
        ->expectsOutput('Publishing Radar Configuration...')
        ->expectsOutput('Publishing Radar Migrations...')
        ->expectsOutput('Radar scaffolding installed successfully.');
});
