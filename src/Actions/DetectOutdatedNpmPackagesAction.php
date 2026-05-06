<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ClassifiesUpdateTypes;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Concerns\RunsReadOnlyCommands;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Enums\DependencyType;
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

    /** @return list<OutdatedPackageFindingData> */
    public function execute(?string $basepath = null): array
    {
        $basepath ??= base_path();

        /** @var array{dependencies?: array<string, string>, devDependencies?: array<string, string>, peerDependencies?: array<string, string>} $packageJson */
        $packageJson = $this->readJson($basepath.'/package.json');

        $outdatedReport = $this->readJson($basepath.'/npm-outdated.json');
        $nodeRunner = NodeRunner::fromProjectPath($basepath);

        if ($outdatedReport === []) {
            $outdatedReport = match ($nodeRunner) {
                NodeRunner::Npm => $this->readCommandJson(['npm', 'outdated', '--json'], $basepath),
                NodeRunner::Pnpm => $this->readCommandJson(['pnpm', 'outdated', '--json'], $basepath),
                NodeRunner::Yarn, NodeRunner::Bun => [],
            };
        }

        $directDependencies = [
            ...$this->directDependencies($packageJson['dependencies'] ?? [], DependencyType::Production),
            ...$this->directDependencies($packageJson['devDependencies'] ?? [], DependencyType::Development),
            ...$this->directDependencies($packageJson['peerDependencies'] ?? [], DependencyType::Peer),
        ];

        $findings = [];

        foreach ($this->normalizeOutdatedReport($outdatedReport) as $name => $outdatedPackage) {
            if (! isset($directDependencies[$name])) {
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
                dependencyType: $directDependencies[$name],
                isDirect: true,
            );

            $findings[] = $finding->withSuggestedCommand(
                $this->buildSafeRecommendation->commandForOutdatedPackage($finding, $nodeRunner),
            );
        }

        return $findings;
    }

    /**
     * Normalizes outdated reports from different package managers.
     * npm outputs an object keyed by package name.
     * pnpm outputs an array of objects.
     *
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

        // npm format: object keyed by package name
        if (is_array($firstValue) && array_key_exists('current', $firstValue)) {
            return $report;
        }

        // pnpm format: array of objects with 'alias' key
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
}
