<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\MergesPackages;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class ParseNpmPackagesAction
{
    use MergesPackages;
    use ReadsJsonFiles;

    /** @return list<PackageData> */
    public function execute(?string $basepath = null): array
    {
        $basepath ??= base_path();

        /** @var array{dependencies?: array<string, string>, devDependencies?: array<string, string>, peerDependencies?: array<string, string>} $packageJson */
        $packageJson = $this->readJson($basepath.'/package.json');

        /** @var array{lockfileVersion?: int, packages?: array<string, array<string, mixed>>, dependencies?: array<string, array<string, mixed>>} $packageLock */
        $packageLock = $this->readJson($basepath.'/package-lock.json');

        $directDependencies = $this->mergeDependencyTypeMaps(
            $this->directDependencies($packageJson['dependencies'] ?? [], DependencyType::Production),
            $this->directDependencies($packageJson['devDependencies'] ?? [], DependencyType::Development),
            $this->directDependencies($packageJson['peerDependencies'] ?? [], DependencyType::Peer),
        );

        $lockedPackages = $this->normalizeLockedPackages($packageLock);

        if ($lockedPackages === []) {
            return $this->packagesFromNodeModules($basepath, $directDependencies);
        }

        $requiredBy = $this->requiredBy($lockedPackages);
        $packages = [];

        foreach ($lockedPackages as $path => $lockedPackage) {
            if ($path === '') {
                continue;
            }

            $name = $this->packageNameFromPath($path);

            if ($name === null) {
                continue;
            }

            $version = $lockedPackage['version'] ?? null;

            if (! is_string($version)) {
                continue;
            }

            $isDirect = isset($directDependencies[$name]) && $this->isTopLevelPackagePath($path, $name);

            $package = new PackageData(
                id: $this->packageId($name, $version, $path, $isDirect),
                ecosystem: Ecosystem::Npm,
                name: $name,
                installedVersion: $version,
                dependencyType: $isDirect ? $directDependencies[$name] : $this->lockedDependencyType($lockedPackage),
                isDirect: $isDirect,
                sourceUrl: $this->sourceUrl($lockedPackage),
                requiredBy: $requiredBy[$name] ?? [],
                path: $path,
            );

            $packages = $this->mergePackage($packages, $package, $package->id);
        }

        return array_values($packages);
    }

    /**
     * @param  array<string, DependencyType>  $directDependencies
     * @return list<PackageData>
     */
    private function packagesFromNodeModules(string $basepath, array $directDependencies): array
    {
        $packages = [];

        foreach ($directDependencies as $name => $dependencyType) {
            $installedPackage = $this->readJson($basepath.'/node_modules/'.$name.'/package.json');
            $version = $installedPackage['version'] ?? null;

            if (! is_string($version)) {
                continue;
            }

            $packages[] = new PackageData(
                id: 'npm-'.$name,
                ecosystem: Ecosystem::Npm,
                name: $name,
                installedVersion: $version,
                dependencyType: $dependencyType,
                isDirect: true,
                sourceUrl: $this->repositoryUrl($installedPackage),
                requiredBy: [],
                path: 'node_modules/'.$name,
            );
        }

        return $packages;
    }

    private function packageId(string $name, string $version, string $path, bool $isDirect): string
    {
        if ($isDirect) {
            return 'npm-'.$name;
        }

        return 'npm-'.$name.'-'.$version.'-'.mb_substr(hash('xxh128', $path), 0, 8);
    }

    /**
     * @param  array{lockfileVersion?: int, packages?: array<string, array<string, mixed>>, dependencies?: array<string, array<string, mixed>>}  $packageLock
     * @return array<string, array<string, mixed>>
     */
    private function normalizeLockedPackages(array $packageLock): array
    {
        $lockfileVersion = $packageLock['lockfileVersion'] ?? 1;

        if ($lockfileVersion >= 2) {
            return $packageLock['packages'] ?? [];
        }

        return $this->normalizeVersionOneDependencies($packageLock['dependencies'] ?? []);
    }

    /**
     * @param  array<string, array<string, mixed>>  $dependencies
     * @return array<string, array<string, mixed>>
     */
    private function normalizeVersionOneDependencies(array $dependencies, string $parentPath = ''): array
    {
        $normalized = [];

        foreach ($dependencies as $name => $dependency) {
            $path = $parentPath === ''
                ? 'node_modules/'.$name
                : $parentPath.'/node_modules/'.$name;

            $normalized[$path] = $dependency;

            $nestedDependencies = $dependency['dependencies'] ?? [];

            if (! is_array($nestedDependencies)) {
                continue;
            }

            /** @var array<string, array<string, mixed>> $nestedDependencies */
            $normalized = [
                ...$normalized,
                ...$this->normalizeVersionOneDependencies($nestedDependencies, $path),
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, array<string, mixed>>  $lockedPackages
     * @return array<string, list<string>>
     */
    private function requiredBy(array $lockedPackages): array
    {
        $requiredBy = [];

        foreach ($lockedPackages as $path => $lockedPackage) {
            if ($path === '') {
                continue;
            }

            $requiringPackage = $this->packageNameFromPath($path);
            $dependencies = $lockedPackage['dependencies'] ?? $lockedPackage['requires'] ?? [];

            if ($requiringPackage === null) {
                continue;
            }

            if (! is_array($dependencies)) {
                continue;
            }

            foreach (array_keys($dependencies) as $dependency) {
                if (! is_string($dependency)) {
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

    private function isTopLevelPackagePath(string $path, string $name): bool
    {
        return $path === 'node_modules/'.$name;
    }

    private function packageNameFromPath(string $path): ?string
    {
        $position = mb_strrpos($path, 'node_modules/');

        if ($position === false) {
            return null;
        }

        return mb_substr($path, $position + mb_strlen('node_modules/'));
    }

    /** @param array<string, mixed> $lockedPackage */
    private function lockedDependencyType(array $lockedPackage): DependencyType
    {
        if (($lockedPackage['peer'] ?? false) === true) {
            return DependencyType::Peer;
        }

        if (($lockedPackage['dev'] ?? false) === true) {
            return DependencyType::Development;
        }

        return DependencyType::Production;
    }

    /** @param array<string, mixed> $lockedPackage */
    private function sourceUrl(array $lockedPackage): ?string
    {
        $resolved = $lockedPackage['resolved'] ?? null;

        return is_string($resolved) ? $resolved : null;
    }

    /** @param array<string, mixed> $installedPackage */
    private function repositoryUrl(array $installedPackage): ?string
    {
        $repository = $installedPackage['repository'] ?? null;

        if (is_string($repository)) {
            return $repository;
        }

        if (! is_array($repository)) {
            return null;
        }

        $url = $repository['url'] ?? null;

        return is_string($url) ? $url : null;
    }
}
