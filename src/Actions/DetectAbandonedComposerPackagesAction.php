<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\AbandonedPackageFindingData;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class DetectAbandonedComposerPackagesAction
{
    use ReadsJsonFiles;

    /** @return list<AbandonedPackageFindingData> */
    public function execute(?string $basepath = null): array
    {
        $basepath ??= base_path();

        /** @var array{require?: array<string, string>, require-dev?: array<string, string>} $composerJson */
        $composerJson = $this->readJson($basepath.'/composer.json');

        /** @var array{packages?: list<array<string, mixed>>, packages-dev?: list<array<string, mixed>>} $composerLock */
        $composerLock = $this->readJson($basepath.'/composer.lock');

        $productionDependencies = $this->composerDirectDependencies($composerJson['require'] ?? [], DependencyType::Production);
        $developmentDependencies = $this->composerDirectDependencies($composerJson['require-dev'] ?? [], DependencyType::Development);

        return [
            ...$this->findings(
                lockedPackages: $composerLock['packages'] ?? [],
                directDependencies: $productionDependencies,
                dependencyType: DependencyType::Production,
            ),
            ...$this->findings(
                lockedPackages: $composerLock['packages-dev'] ?? [],
                directDependencies: $developmentDependencies,
                dependencyType: DependencyType::Development,
            ),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lockedPackages
     * @param  array<string, DependencyType>  $directDependencies
     * @return list<AbandonedPackageFindingData>
     */
    private function findings(array $lockedPackages, array $directDependencies, DependencyType $dependencyType): array
    {
        $findings = [];

        foreach ($lockedPackages as $lockedPackage) {
            $abandoned = $lockedPackage['abandoned'] ?? false;

            if ($abandoned === false) {
                continue;
            }

            $name = $lockedPackage['name'] ?? null;
            $version = $lockedPackage['version'] ?? null;

            if (! is_string($name)) {
                continue;
            }

            if (! is_string($version)) {
                continue;
            }

            $replacementPackage = is_string($abandoned) ? $abandoned : null;

            $findings[] = new AbandonedPackageFindingData(
                id: 'composer-'.$name,
                ecosystem: Ecosystem::Composer,
                packageName: $name,
                installedVersion: ltrim($version, 'v'),
                dependencyType: $dependencyType,
                isDirect: isset($directDependencies[$name]),
                replacementPackage: $replacementPackage,
                recommendation: $this->recommendation($name, $replacementPackage),
            );
        }

        return $findings;
    }

    private function recommendation(string $packageName, ?string $replacementPackage): string
    {
        if ($replacementPackage) {
            return sprintf('Replace %s with %s after reviewing compatibility.', $packageName, $replacementPackage);
        }

        return sprintf('Review %s and replace it with a maintained alternative when possible.', $packageName);
    }
}
