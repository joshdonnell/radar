<?php

declare(strict_types=1);

it('installs radar scaffolding', function (): void {
    $this->artisan('radar:install')
        ->assertSuccessful()
        ->expectsOutput('Publishing config...')
        ->expectsOutput('Publishing migrations...')
        ->expectsOutput('Publishing assets...')
        ->expectsOutput('radar has been installed!');
});
