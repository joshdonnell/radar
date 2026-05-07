<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ClassifiesUpdateTypes;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Concerns\RunsReadOnlyCommands;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class DetectOutdatedComposerPackagesAction
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
        /** @var array{installed?: list<array<string, mixed>>} $outdatedReport */
        $outdatedReport = $this->readJson($basepath.'/composer-outdated.json');

        if ($outdatedReport === []) {
            /** @var array{installed?: list<array<string, mixed>>} $outdatedReport */
            $outdatedReport = $this->readCommandJson(['composer', 'outdated', '--direct', '--format=json'], $basepath);
        }

        $directPackages = $this->directPackagesByName($packages);
        $findings = [];

        foreach ($outdatedReport['installed'] ?? [] as $outdatedPackage) {
            $name = $outdatedPackage['name'] ?? null;
            $currentVersion = $outdatedPackage['version'] ?? null;
            $latestVersion = $outdatedPackage['latest'] ?? null;

            if (! is_string($name)) {
                continue;
            }

            $package = $directPackages[$name] ?? null;

            if (! $package instanceof PackageData) {
                continue;
            }

            if (! is_string($currentVersion)) {
                continue;
            }

            if (! is_string($latestVersion)) {
                continue;
            }

            $finding = new OutdatedPackageFindingData(
                id: 'composer-'.$name.'-outdated',
                ecosystem: Ecosystem::Composer,
                packageName: $name,
                currentVersion: ltrim($currentVersion, 'v'),
                latestVersion: ltrim($latestVersion, 'v'),
                updateType: $this->classifyUpdateType($currentVersion, $latestVersion),
                dependencyType: $package->dependencyType,
                isDirect: true,
            );

            $findings[] = $finding->withSuggestedCommand(
                $this->buildSafeRecommendation->commandForOutdatedPackage($finding),
            );
        }

        return $findings;
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
