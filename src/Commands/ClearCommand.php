<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;
use JoshDonnell\Radar\Models\RadarScan;

final class ClearCommand extends Command
{
    public $signature = 'radar:clear {--force : Clear scan history without confirmation}';

    public $description = 'Clear all stored Radar scan history';

    public function handle(): int
    {
        $query = RadarScan::query();

        $scanCount = $query->count();

        if ($scanCount === 0) {
            $this->components->info('No Radar scans to clear.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->components->confirm(sprintf('Clear %d Radar scan(s)?', $scanCount), false)) {
            $this->components->info('Radar scan history was not cleared.');

            return self::SUCCESS;
        }

        $query->delete();

        $this->components->info(sprintf('Cleared %d Radar scan(s).', $scanCount));

        return self::SUCCESS;
    }
}
