<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

use JoshDonnell\Radar\Data\PackageData;

trait IndexesPackages
{
    /**
     * @param  list<PackageData>  $packages
     * @return array<string, PackageData>
     */
    private function packagesByName(array $packages): array
    {
        $mapped = [];

        foreach ($packages as $package) {
            $mapped[$package->name] = $package;
        }

        return $mapped;
    }

    /**
     * @param  list<PackageData>  $packages
     * @return array<string, PackageData>
     */
    private function directPackagesByName(array $packages): array
    {
        return $this->packagesByName(array_values(array_filter(
            $packages,
            static fn (PackageData $package): bool => $package->isDirect === true,
        )));
    }

    /**
     * @param  list<PackageData>  $packages
     * @return array<string, list<PackageData>>
     */
    private function packagesGroupedByName(array $packages): array
    {
        $grouped = [];

        foreach ($packages as $package) {
            $grouped[$package->name] ??= [];
            $grouped[$package->name][] = $package;
        }

        return $grouped;
    }
}
