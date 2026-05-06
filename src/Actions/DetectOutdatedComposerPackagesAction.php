<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ClassifiesUpdateTypes;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Concerns\RunsReadOnlyCommands;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class DetectOutdatedComposerPackagesAction
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

        /** @var array{require?: array<string, string>, require-dev?: array<string, string>} $composerJson */
        $composerJson = $this->readJson($basepath.'/composer.json');

        /** @var array{installed?: list<array<string, mixed>>} $outdatedReport */
        $outdatedReport = $this->readJson($basepath.'/composer-outdated.json');

        if ($outdatedReport === []) {
            /** @var array{installed?: list<array<string, mixed>>} $outdatedReport */
            $outdatedReport = $this->readCommandJson(['composer', 'outdated', '--direct', '--format=json'], $basepath);
        }

        $directDependencies = [
            ...$this->composerDirectDependencies($composerJson['require'] ?? [], DependencyType::Production),
            ...$this->composerDirectDependencies($composerJson['require-dev'] ?? [], DependencyType::Development),
        ];

        $findings = [];

        foreach ($outdatedReport['installed'] ?? [] as $outdatedPackage) {
            $name = $outdatedPackage['name'] ?? null;
            $currentVersion = $outdatedPackage['version'] ?? null;
            $latestVersion = $outdatedPackage['latest'] ?? null;

            if (! is_string($name)) {
                continue;
            }

            if (! isset($directDependencies[$name])) {
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
                dependencyType: $directDependencies[$name],
                isDirect: true,
            );

            $findings[] = $finding->withSuggestedCommand(
                $this->buildSafeRecommendation->commandForOutdatedPackage($finding),
            );
        }

        return $findings;
    }
}
