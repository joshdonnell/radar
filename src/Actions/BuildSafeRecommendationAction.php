<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;

final readonly class BuildSafeRecommendationAction
{
    public function forVulnerability(VulnerabilityFindingData $finding): string
    {
        return $this->forVulnerabilityFields(
            isDirect: $finding->isDirect,
            packageName: $finding->packageName,
        );
    }

    public function forVulnerabilityFields(bool $isDirect, string $packageName): string
    {
        if (! $isDirect) {
            return sprintf(
                'Review which direct dependency requires %s before updating. Prefer updating the parent package rather than editing the lock file manually.',
                $packageName,
            );
        }

        return 'Review the advisory before updating.';
    }

    public function commandForVulnerabilityFields(bool $isDirect, string $packageName, Ecosystem $ecosystem, ?NodeRunner $nodeRunner = null): ?string
    {
        if (! $isDirect) {
            return null;
        }

        return match ($ecosystem) {
            Ecosystem::Composer => sprintf('composer update %s --with-dependencies', $packageName),
            Ecosystem::Npm => ($nodeRunner ?? NodeRunner::Npm)->updateCommand($packageName),
        };
    }

    public function forOutdatedPackage(OutdatedPackageFindingData $finding): string
    {
        if (! $finding->isDirect) {
            return sprintf(
                'Review which direct dependency requires %s before updating this transitive package.',
                $finding->packageName,
            );
        }

        return 'Review the changelog before updating.';
    }

    public function commandForOutdatedPackage(OutdatedPackageFindingData $finding, ?NodeRunner $nodeRunner = null): ?string
    {
        if (! $finding->isDirect) {
            return null;
        }

        return match ($finding->ecosystem) {
            Ecosystem::Composer => sprintf('composer update %s --with-dependencies', $finding->packageName),
            Ecosystem::Npm => ($nodeRunner ?? NodeRunner::Npm)->updateCommand($finding->packageName),
        };
    }
}
