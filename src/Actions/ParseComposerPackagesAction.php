<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\MergesPackages;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class ParseComposerPackagesAction
{
    use MergesPackages;
    use ReadsJsonFiles;

    /** @return list<PackageData> */
    public function execute(?string $basepath = null): array
    {
        $basepath ??= base_path();

        /** @var array{require?: array<string, string>, require-dev?: array<string, string>} $composerJson */
        $composerJson = $this->readJson($basepath.'/composer.json');

        /** @var array{packages?: list<array<string, mixed>>, packages-dev?: list<array<string, mixed>>} $composerLock */
        $composerLock = $this->readJson($basepath.'/composer.lock');

        $productionDependencies = $this->composerDirectDependencies($composerJson['require'] ?? [], DependencyType::Production);
        $developmentDependencies = $this->composerDirectDependencies($composerJson['require-dev'] ?? [], DependencyType::Development);
        $directDependencies = $this->mergeDependencyTypeMaps($productionDependencies, $developmentDependencies);
        $lockedPackages = [
            ...($composerLock['packages'] ?? []),
            ...($composerLock['packages-dev'] ?? []),
        ];

        if ($lockedPackages === []) {
            return $this->packagesFromInstalledJson(
                installedJson: $this->readJson($basepath.'/vendor/composer/installed.json'),
                directDependencies: $directDependencies,
            );
        }

        $requiredBy = $this->requiredBy($lockedPackages);

        return $this->mergeDuplicates([
            ...$this->packages(
                lockedPackages: $composerLock['packages'] ?? [],
                directDependencies: $directDependencies,
                dependencyType: DependencyType::Production,
                requiredBy: $requiredBy,
            ),
            ...$this->packages(
                lockedPackages: $composerLock['packages-dev'] ?? [],
                directDependencies: $directDependencies,
                dependencyType: DependencyType::Development,
                requiredBy: $requiredBy,
            ),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $lockedPackages
     * @return array<string, list<string>>
     */
    private function requiredBy(array $lockedPackages): array
    {
        $requiredBy = [];

        foreach ($lockedPackages as $lockedPackage) {
            $requiringPackage = $lockedPackage['name'] ?? null;
            $dependencies = $lockedPackage['require'] ?? [];
            if (! is_string($requiringPackage)) {
                continue;
            }

            if (! is_array($dependencies)) {
                continue;
            }

            foreach (array_keys($dependencies) as $dependency) {
                if (! is_string($dependency)) {
                    continue;
                }

                if ($dependency === 'php') {
                    continue;
                }

                if (str_starts_with($dependency, 'ext-')) {
                    continue;
                }

                $requiredBy[$dependency] ??= [];
                $requiredBy[$dependency][] = $requiringPackage;
            }
        }

        foreach ($requiredBy as $dependency => $requiringPackages) {
            $requiredBy[$dependency] = array_values(array_unique($requiringPackages));
        }

        return $requiredBy;
    }

    /**
     * @param  list<array<string, mixed>>  $lockedPackages
     * @param  array<string, DependencyType>  $directDependencies
     * @param  array<string, list<string>>  $requiredBy
     * @return list<PackageData>
     */
    private function packages(
        array $lockedPackages,
        array $directDependencies,
        DependencyType $dependencyType,
        array $requiredBy,
    ): array {
        $packages = [];

        foreach ($lockedPackages as $lockedPackage) {
            $name = $lockedPackage['name'] ?? null;
            $version = $lockedPackage['version'] ?? null;
            if (! is_string($name)) {
                continue;
            }

            if (! is_string($version)) {
                continue;
            }

            $packages[] = new PackageData(
                id: 'composer-'.$name,
                ecosystem: Ecosystem::Composer,
                name: $name,
                installedVersion: ltrim($version, 'v'),
                dependencyType: $directDependencies[$name] ?? $dependencyType,
                isDirect: isset($directDependencies[$name]),
                sourceUrl: $this->sourceUrl($lockedPackage),
                requiredBy: $requiredBy[$name] ?? [],
            );
        }

        return $packages;
    }

    /**
     * @param  array<string, mixed>  $installedJson
     * @param  array<string, DependencyType>  $directDependencies
     * @return list<PackageData>
     */
    private function packagesFromInstalledJson(
        array $installedJson,
        array $directDependencies,
    ): array {
        $installedPackages = $installedJson['packages'] ?? [];

        if (! is_array($installedPackages)) {
            return [];
        }

        /** @var list<array<string, mixed>> $installedPackages */
        $requiredBy = $this->requiredBy($installedPackages);
        $developmentPackageNames = $this->developmentPackageNames($installedJson);
        $packages = [];

        foreach ($installedPackages as $installedPackage) {
            $name = $installedPackage['name'] ?? null;
            $version = $installedPackage['version'] ?? null;

            if (! is_string($name)) {
                continue;
            }

            if (! is_string($version)) {
                continue;
            }

            $packages[] = new PackageData(
                id: 'composer-'.$name,
                ecosystem: Ecosystem::Composer,
                name: $name,
                installedVersion: ltrim($version, 'v'),
                dependencyType: $directDependencies[$name] ?? $this->installedDependencyType($name, $developmentPackageNames),
                isDirect: isset($directDependencies[$name]),
                sourceUrl: $this->sourceUrl($installedPackage),
                requiredBy: $requiredBy[$name] ?? [],
            );
        }

        return $this->mergeDuplicates($packages);
    }

    /**
     * @param  array<string, mixed>  $installedJson
     * @return list<string>
     */
    private function developmentPackageNames(array $installedJson): array
    {
        $packageNames = $installedJson['dev-package-names'] ?? [];

        if (! is_array($packageNames)) {
            return [];
        }

        $developmentPackageNames = [];

        foreach ($packageNames as $packageName) {
            if (is_string($packageName)) {
                $developmentPackageNames[] = $packageName;
            }
        }

        return $developmentPackageNames;
    }

    /** @param list<string> $developmentPackageNames */
    private function installedDependencyType(string $packageName, array $developmentPackageNames): DependencyType
    {
        if (in_array($packageName, $developmentPackageNames, true)) {
            return DependencyType::Development;
        }

        return DependencyType::Production;
    }

    /**
     * @param  list<PackageData>  $packages
     * @return list<PackageData>
     */
    private function mergeDuplicates(array $packages): array
    {
        $merged = [];

        foreach ($packages as $package) {
            $merged = $this->mergePackage($merged, $package, $package->ecosystem->value.'-'.$package->name);
        }

        return array_values($merged);
    }

    /** @param array<string, mixed> $lockedPackage */
    private function sourceUrl(array $lockedPackage): ?string
    {
        $source = $lockedPackage['source'] ?? null;

        if (! is_array($source)) {
            return null;
        }

        $url = $source['url'] ?? null;

        if (! is_string($url)) {
            return null;
        }

        return str_ends_with($url, '.git') ? mb_substr($url, 0, -4) : $url;
    }
}
