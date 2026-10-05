<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ClassifiesUpdateTypes;
use JoshDonnell\Radar\Concerns\IndexesPackages;
use JoshDonnell\Radar\Concerns\ReadsArrayValues;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\DetectionResultData;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;
use JoshDonnell\Radar\Enums\ScanCheck;
use JoshDonnell\Radar\Exceptions\CommandFailedException;
use JoshDonnell\Radar\Support\ReadOnlyCommandRunner;

final readonly class DetectOutdatedNpmPackagesAction
{
    use ClassifiesUpdateTypes;
    use IndexesPackages;
    use ReadsArrayValues;
    use ReadsJsonFiles;

    public function __construct(
        private BuildSafeRecommendationAction $buildSafeRecommendation,
        private ReadOnlyCommandRunner $commandRunner,
    ) {}

    public function prepare(string $basepath): void
    {
        if ($this->hasReportFile($basepath)) {
            return;
        }

        $command = $this->command(NodeRunner::fromProjectPath($basepath));

        if ($command === null) {
            return;
        }

        $this->commandRunner->start($command, $basepath);
    }

    /**
     * @param  list<PackageData>  $packages
     * @return DetectionResultData<OutdatedPackageFindingData>
     */
    public function execute(string $basepath, array $packages): DetectionResultData
    {
        $nodeRunner = NodeRunner::fromProjectPath($basepath);

        try {
            $outdatedReport = $this->outdatedReport($basepath, $nodeRunner);
        } catch (CommandFailedException $commandFailedException) {
            return DetectionResultData::failed(Ecosystem::Npm, ScanCheck::Outdated, $commandFailedException->getMessage());
        }

        if ($outdatedReport === null) {
            return DetectionResultData::failed(
                Ecosystem::Npm,
                ScanCheck::Outdated,
                sprintf('Radar cannot check %s projects for outdated packages yet.', ucfirst($nodeRunner->value)),
            );
        }

        $directPackages = $this->directPackagesByName($packages);
        $findings = [];

        foreach ($this->normalizeOutdatedReport($outdatedReport) as $name => $outdatedPackage) {
            $package = $directPackages[$name] ?? null;

            if (! $package instanceof PackageData) {
                continue;
            }

            if (! is_array($outdatedPackage)) {
                continue;
            }

            $currentVersion = self::stringValue($outdatedPackage, 'current');
            $latestVersion = self::stringValue($outdatedPackage, 'latest');

            if ($currentVersion === null) {
                continue;
            }

            if ($latestVersion === null) {
                continue;
            }

            $finding = new OutdatedPackageFindingData(
                id: "npm-{$name}-outdated",
                ecosystem: Ecosystem::Npm,
                packageName: $name,
                currentVersion: $currentVersion,
                latestVersion: $latestVersion,
                updateType: $this->classifyUpdateType($currentVersion, $latestVersion),
                dependencyType: $package->dependencyType,
                isDirect: true,
            );

            $findings[] = $finding->withSuggestedCommand(
                $this->buildSafeRecommendation->commandForOutdatedPackage($finding, $nodeRunner),
            );
        }

        return new DetectionResultData($findings);
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws CommandFailedException
     */
    private function outdatedReport(string $basepath, NodeRunner $nodeRunner): ?array
    {
        if ($this->hasReportFile($basepath)) {
            return $this->readJson($this->reportFile($basepath));
        }

        $command = $this->command($nodeRunner);

        if ($command === null) {
            return null;
        }

        return $this->commandRunner->json($command, $basepath);
    }

    /** @return list<string>|null */
    private function command(NodeRunner $nodeRunner): ?array
    {
        return match ($nodeRunner) {
            NodeRunner::Npm => ['npm', 'outdated', '--json'],
            NodeRunner::Pnpm => ['pnpm', 'outdated', '--json'],
            NodeRunner::Yarn, NodeRunner::Bun => null,
        };
    }

    private function hasReportFile(string $basepath): bool
    {
        return file_exists($this->reportFile($basepath));
    }

    private function reportFile(string $basepath): string
    {
        return "{$basepath}/npm-outdated.json";
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function normalizeOutdatedReport(array $report): array
    {
        if ($report === []) {
            return [];
        }

        $firstKey = array_key_first($report);
        $firstValue = $report[$firstKey] ?? null;

        if (is_array($firstValue) && array_key_exists('current', $firstValue)) {
            return $report;
        }

        if (is_array($firstValue) && array_key_exists('alias', $firstValue)) {
            $normalized = [];

            foreach ($report as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $name = self::stringValue($item, 'alias') ?? self::stringValue($item, 'name');

                if ($name === null) {
                    continue;
                }

                $normalized[$name] = [
                    'current' => $item['current'] ?? null,
                    'latest' => $item['latest'] ?? null,
                ];
            }

            return $normalized;
        }

        return [];
    }
}
