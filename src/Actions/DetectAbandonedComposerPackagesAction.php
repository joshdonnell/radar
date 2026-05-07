<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\AbandonedPackageFindingData;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class DetectAbandonedComposerPackagesAction
{
    use ReadsJsonFiles;

    /**
     * @param  list<PackageData>  $packages
     * @return list<AbandonedPackageFindingData>
     */
    public function execute(string $basepath, array $packages): array
    {
        /** @var array{packages?: list<array<string, mixed>>, packages-dev?: list<array<string, mixed>>} $composerLock */
        $composerLock = $this->readJson($basepath.'/composer.lock');
        $packagesByName = $this->packagesByName($packages);

        return [
            ...$this->findings($composerLock['packages'] ?? [], $packagesByName),
            ...$this->findings($composerLock['packages-dev'] ?? [], $packagesByName),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lockedPackages
     * @param  array<string, PackageData>  $packagesByName
     * @return list<AbandonedPackageFindingData>
     */
    private function findings(array $lockedPackages, array $packagesByName): array
    {
        $findings = [];

        foreach ($lockedPackages as $lockedPackage) {
            $abandoned = $lockedPackage['abandoned'] ?? false;

            if ($abandoned === false) {
                continue;
            }

            $name = $lockedPackage['name'] ?? null;

            if (! is_string($name)) {
                continue;
            }

            $package = $packagesByName[$name] ?? null;

            if (! $package instanceof PackageData) {
                continue;
            }

            $replacementPackage = is_string($abandoned) ? $abandoned : null;

            $findings[] = new AbandonedPackageFindingData(
                id: 'composer-'.$name,
                ecosystem: Ecosystem::Composer,
                packageName: $name,
                installedVersion: $package->installedVersion,
                dependencyType: $package->dependencyType,
                isDirect: $package->isDirect === true,
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
