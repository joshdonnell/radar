<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;
use JoshDonnell\Radar\Actions\RunScanAction;

final class ScanCommand extends Command
{
    public $signature = 'radar:scan {--path= : Base path to scan. Defaults to the application base path}';

    public $description = 'Scan application dependencies and store a Radar snapshot';

    public function __construct(
        private readonly RunScanAction $runScan,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->option('path');
        $basepath = is_string($path) && $path !== '' ? $path : base_path();

        if (! is_dir($basepath)) {
            $this->components->error(sprintf('The path [%s] does not exist.', $basepath));

            return self::FAILURE;
        }

        $scan = $this->runScan->execute($basepath);

        $payload = $scan->payload;
        $outdated = $payload['outdated'] ?? [];
        $abandoned = $payload['abandoned'] ?? [];

        $this->components->info(sprintf(
            'Stored Radar scan %s with %d package(s), %d vulnerability finding(s), %d outdated package finding(s), and %d abandoned package finding(s).',
            $scan->id,
            $scan->package_count,
            $scan->vulnerability_count,
            is_countable($outdated) ? count($outdated) : 0,
            is_countable($abandoned) ? count($abandoned) : 0,
        ));

        return self::SUCCESS;
    }
}
