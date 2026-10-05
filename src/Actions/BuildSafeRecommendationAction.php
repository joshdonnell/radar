<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;

final readonly class BuildSafeRecommendationAction
{
    public function forVulnerability(bool $isDirect, string $packageName): string
    {
        if (! $isDirect) {
            return sprintf(
                '%s is a transitive dependency. Try the suggested command first, and if it cannot reach a patched version, update the package that requires it rather than editing the lock file manually.',
                $packageName,
            );
        }

        return 'Review the advisory before updating.';
    }

    /** @param list<string> $requiredBy */
    public function commandForVulnerability(
        Ecosystem $ecosystem,
        string $packageName,
        bool $isDirect,
        array $requiredBy = [],
        ?NodeRunner $nodeRunner = null,
    ): ?string {
        if ($isDirect) {
            return $this->updateCommand($ecosystem, $packageName, $nodeRunner);
        }

        $nodeRunner ??= NodeRunner::Npm;

        return match ($ecosystem) {
            Ecosystem::Composer => sprintf('composer update %s', $packageName),
            Ecosystem::Npm => $nodeRunner->transitiveFixCommand($packageName)
                ?? $this->parentCommands($ecosystem, $requiredBy, $nodeRunner)[0]
                ?? null,
        };
    }

    /**
     * Updating a parent package is the alternative to the primary command for
     * a transitive vulnerability, so these are only offered for those.
     *
     * @param  list<string>  $requiredBy
     * @return list<string>
     */
    public function alternativeCommandsForVulnerability(
        Ecosystem $ecosystem,
        string $packageName,
        bool $isDirect,
        array $requiredBy = [],
        ?NodeRunner $nodeRunner = null,
    ): array {
        if ($isDirect) {
            return [];
        }

        $primaryCommand = $this->commandForVulnerability($ecosystem, $packageName, $isDirect, $requiredBy, $nodeRunner);

        return array_values(array_filter(
            $this->parentCommands($ecosystem, $requiredBy, $nodeRunner),
            static fn (string $command): bool => $command !== $primaryCommand,
        ));
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

        return $this->updateCommand($finding->ecosystem, $finding->packageName, $nodeRunner);
    }

    /**
     * @param  list<string>  $requiredBy
     * @return list<string>
     */
    private function parentCommands(Ecosystem $ecosystem, array $requiredBy, ?NodeRunner $nodeRunner): array
    {
        return array_map(
            fn (string $parent): string => $this->updateCommand($ecosystem, $parent, $nodeRunner),
            $requiredBy,
        );
    }

    private function updateCommand(Ecosystem $ecosystem, string $packageName, ?NodeRunner $nodeRunner): string
    {
        return match ($ecosystem) {
            Ecosystem::Composer => sprintf('composer update %s --with-dependencies', $packageName),
            Ecosystem::Npm => ($nodeRunner ?? NodeRunner::Npm)->updateCommand($packageName),
        };
    }
}
