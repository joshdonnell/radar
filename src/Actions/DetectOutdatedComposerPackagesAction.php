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
use JoshDonnell\Radar\Enums\ScanCheck;
use JoshDonnell\Radar\Exceptions\CommandFailedException;
use JoshDonnell\Radar\Support\ReadOnlyCommandRunner;

final readonly class DetectOutdatedComposerPackagesAction
{
    use ClassifiesUpdateTypes;
    use IndexesPackages;
    use ReadsArrayValues;
    use ReadsJsonFiles;

    private const array COMMAND = ['composer', 'outdated', '--direct', '--format=json'];

    public function __construct(
        private BuildSafeRecommendationAction $buildSafeRecommendation,
        private ReadOnlyCommandRunner $commandRunner,
    ) {}

    public function prepare(string $basepath): void
    {
        if ($this->hasReportFile($basepath)) {
            return;
        }

        $this->commandRunner->start(self::COMMAND, $basepath);
    }

    /**
     * @param  list<PackageData>  $packages
     * @return DetectionResultData<OutdatedPackageFindingData>
     */
    public function execute(string $basepath, array $packages): DetectionResultData
    {
        try {
            $outdatedReport = $this->hasReportFile($basepath)
                ? $this->readJson($this->reportFile($basepath))
                : $this->commandRunner->json(self::COMMAND, $basepath);
        } catch (CommandFailedException $commandFailedException) {
            return DetectionResultData::failed(Ecosystem::Composer, ScanCheck::Outdated, $commandFailedException->getMessage());
        }

        $directPackages = $this->directPackagesByName($packages);
        $findings = [];

        foreach (self::recordListValue($outdatedReport, 'installed') as $outdatedPackage) {
            $name = self::stringValue($outdatedPackage, 'name');
            $currentVersion = self::stringValue($outdatedPackage, 'version');
            $latestVersion = self::stringValue($outdatedPackage, 'latest');

            if ($name === null) {
                continue;
            }

            $package = $directPackages[$name] ?? null;

            if (! $package instanceof PackageData) {
                continue;
            }

            if ($currentVersion === null) {
                continue;
            }

            if ($latestVersion === null) {
                continue;
            }

            $finding = new OutdatedPackageFindingData(
                id: "composer-{$name}-outdated",
                ecosystem: Ecosystem::Composer,
                packageName: $name,
                currentVersion: mb_ltrim($currentVersion, 'v'),
                latestVersion: mb_ltrim($latestVersion, 'v'),
                updateType: $this->classifyUpdateType($currentVersion, $latestVersion),
                dependencyType: $package->dependencyType,
                isDirect: true,
            );

            $findings[] = $finding->withSuggestedCommand(
                $this->buildSafeRecommendation->commandForOutdatedPackage($finding),
            );
        }

        return new DetectionResultData($findings);
    }

    private function hasReportFile(string $basepath): bool
    {
        return file_exists($this->reportFile($basepath));
    }

    private function reportFile(string $basepath): string
    {
        return "{$basepath}/composer-outdated.json";
    }
}
