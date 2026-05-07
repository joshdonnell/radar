<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Commands;

use Illuminate\Console\Command;
use JoshDonnell\Radar\Actions\RunScanAction;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Support\CiScanReport;

final class ScanCommand extends Command
{
    public $signature = 'radar:scan
        {--path= : Base path to scan. Defaults to the application base path}
        {--ci : Run in CI mode with pipeline friendly output and build failing exit codes}
        {--severity=low : Minimum vulnerability severity that fails CI. Supported values: low, medium, high, critical}';

    public $description = 'Scan application dependencies and store a Radar snapshot';

    public function __construct(
        private readonly RunScanAction $runScan,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $ciMode = $this->option('ci') === true;
        $path = $this->option('path');
        $basepath = is_string($path) && $path !== '' ? $path : base_path();

        if (! is_dir($basepath)) {
            $this->components->error(sprintf('The path [%s] does not exist.', $basepath));

            return $ciMode ? self::INVALID : self::FAILURE;
        }

        if ($ciMode) {
            return $this->handleCiScan($basepath);
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

    private function handleCiScan(string $basepath): int
    {
        $severity = $this->option('severity');
        $severityThreshold = VulnerabilitySeverity::fromThreshold(
            is_string($severity) && $severity !== ''
                ? $severity
                : VulnerabilitySeverity::Low->value,
        );

        if (! $severityThreshold instanceof VulnerabilitySeverity) {
            $this->components->error(sprintf(
                'Unsupported CI severity threshold. Supported severities are: %s.',
                implode(', ', VulnerabilitySeverity::thresholdValues()),
            ));

            return self::INVALID;
        }

        $report = new CiScanReport($this->runScan->execute($basepath), $severityThreshold);

        $this->output->writeln($report->lines());

        return $report->exitCode();
    }
}
