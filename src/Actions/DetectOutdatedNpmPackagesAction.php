<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ClassifiesUpdateTypes;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Concerns\RunsReadOnlyCommands;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;

final readonly class DetectOutdatedNpmPackagesAction
{
    use ClassifiesUpdateTypes;
    use ReadsJsonFiles;
    use RunsReadOnlyCommands;

    public function __construct(
        private BuildSafeRecommendationAction $buildSafeRecommendation,
    ) {}

    /**
     * @param  list<PackageData>  $packages
     * @return list<OutdatedPackageFindingData>
     */
    public function execute(string $basepath, array $packages): array
    {
        $outdatedReport = $this->readJson($basepath.'/npm-outdated.json');
        $nodeRunner = NodeRunner::fromProjectPath($basepath);

        if ($outdatedReport === []) {
            $outdatedReport = match ($nodeRunner) {
                NodeRunner::Npm => $this->readCommandJson(['npm', 'outdated', '--json'], $basepath),
                NodeRunner::Pnpm => $this->readCommandJson(['pnpm', 'outdated', '--json'], $basepath),
                NodeRunner::Yarn, NodeRunner::Bun => [],
            };
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

            $currentVersion = $outdatedPackage['current'] ?? null;
            $latestVersion = $outdatedPackage['latest'] ?? null;
            if (! is_string($currentVersion)) {
                continue;
            }

            if (! is_string($latestVersion)) {
                continue;
            }

            $finding = new OutdatedPackageFindingData(
                id: 'npm-'.$name.'-outdated',
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

        return $findings;
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

                $name = is_string($item['alias'] ?? null) ? $item['alias'] : (is_string($item['name'] ?? null) ? $item['name'] : null);

                if (! is_string($name)) {
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

    /**
     * @param  list<PackageData>  $packages
     * @return array<string, PackageData>
     */
    private function directPackagesByName(array $packages): array
    {
        $directPackages = [];

        foreach ($packages as $package) {
            if ($package->isDirect !== true) {
                continue;
            }

            $directPackages[$package->name] = $package;
        }

        return $directPackages;
    }
}
