<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;

final class UpgradeCommand extends Command
{
    public $signature = 'radar:upgrade';

    public $description = 'Re-publish Radar dashboard assets after upgrading the package';

    public function handle(): int
    {
        $this->call('vendor:publish', [
            '--tag' => 'radar-assets',
            '--force' => true,
        ]);

        $this->components->info('Radar assets are up to date.');

        return self::SUCCESS;
    }
}
