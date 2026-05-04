<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('radar:install')]
final class InstallCommand extends Command
{
    protected $description = 'Install all of the Radar components';

    public function handle(): void
    {
        $this->comment('Publishing Radar Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'radar-provider']);

        $this->comment('Publishing Radar Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'radar-config']);

        $this->comment('Publishing Radar Migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'radar-migrations']);

        $this->info('Radar scaffolding installed successfully.');
    }
}
