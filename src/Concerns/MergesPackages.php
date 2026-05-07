<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\DependencyType;

trait MergesPackages
{
    private function mergeDependencyType(DependencyType $first, DependencyType $second): DependencyType
    {
        if ($first === DependencyType::Production || $second === DependencyType::Production) {
            return DependencyType::Production;
        }

        if ($first === DependencyType::Peer || $second === DependencyType::Peer) {
            return DependencyType::Peer;
        }

        return DependencyType::Development;
    }

    /**
     * @param  array<string, DependencyType>  ...$dependencyTypeMaps
     * @return array<string, DependencyType>
     */
    private function mergeDependencyTypeMaps(array ...$dependencyTypeMaps): array
    {
        $merged = [];

        foreach ($dependencyTypeMaps as $dependencyTypeMap) {
            foreach ($dependencyTypeMap as $package => $dependencyType) {
                if (! isset($merged[$package])) {
                    $merged[$package] = $dependencyType;

                    continue;
                }

                $merged[$package] = $this->mergeDependencyType($merged[$package], $dependencyType);
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, PackageData>  $packages
     * @return array<string, PackageData>
     */
    private function mergePackage(array $packages, PackageData $package, string $key): array
    {
        $existingPackage = $packages[$key] ?? null;

        if (! $existingPackage instanceof PackageData) {
            $packages[$key] = $package;

            return $packages;
        }

        $packages[$key] = new PackageData(
            id: $existingPackage->id,
            ecosystem: $existingPackage->ecosystem,
            name: $existingPackage->name,
            installedVersion: $existingPackage->installedVersion,
            dependencyType: $this->mergeDependencyType($existingPackage->dependencyType, $package->dependencyType),
            isDirect: $existingPackage->isDirect === true || $package->isDirect === true,
            sourceUrl: $existingPackage->sourceUrl ?? $package->sourceUrl,
            requiredBy: array_values(array_unique([
                ...$existingPackage->requiredBy,
                ...$package->requiredBy,
            ])),
            path: $existingPackage->path ?? $package->path,
        );

        return $packages;
    }
}
