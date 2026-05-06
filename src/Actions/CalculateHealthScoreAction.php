<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Data\AbandonedPackageFindingData;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\UpdateType;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;

final readonly class CalculateHealthScoreAction
{
    /**
     * @param  list<VulnerabilityFindingData>  $vulnerabilities
     * @param  list<OutdatedPackageFindingData>  $outdatedPackages
     * @param  list<AbandonedPackageFindingData>  $abandonedPackages
     */
    public function execute(array $vulnerabilities = [], array $outdatedPackages = [], array $abandonedPackages = []): int
    {
        $penalty = 0;

        foreach ($vulnerabilities as $vulnerability) {
            $penalty += $this->vulnerabilityPenalty($vulnerability);
        }

        foreach ($outdatedPackages as $outdatedPackage) {
            $penalty += $this->outdatedPackagePenalty($outdatedPackage);
        }

        foreach ($abandonedPackages as $abandonedPackage) {
            $penalty += $this->abandonedPackagePenalty($abandonedPackage);
        }

        return max(0, 100 - $penalty);
    }

    private function vulnerabilityPenalty(VulnerabilityFindingData $vulnerability): int
    {
        return $this->vulnerabilitySeverityPenalty(
            severity: $vulnerability->severity->value,
            isDirect: $vulnerability->isDirect,
        );
    }

    private function vulnerabilitySeverityPenalty(string $severity, bool $isDirect): int
    {
        $penalty = match ($severity) {
            VulnerabilitySeverity::Critical->value => 20,
            VulnerabilitySeverity::High->value => 12,
            VulnerabilitySeverity::Medium->value => 6,
            VulnerabilitySeverity::Low->value => 3,
            default => 1,
        };

        return $isDirect ? $penalty : (int) ceil($penalty / 2);
    }

    private function outdatedPackagePenalty(OutdatedPackageFindingData $outdatedPackage): int
    {
        return $this->outdatedPackageUpdatePenalty(
            updateType: $outdatedPackage->updateType->value,
            isDirect: $outdatedPackage->isDirect,
        );
    }

    private function abandonedPackagePenalty(AbandonedPackageFindingData $abandonedPackage): int
    {
        return $this->abandonedPackageDirectnessPenalty($abandonedPackage->isDirect);
    }

    private function abandonedPackageDirectnessPenalty(bool $isDirect): int
    {
        return $isDirect ? 6 : 3;
    }

    private function outdatedPackageUpdatePenalty(string $updateType, bool $isDirect): int
    {
        $penalty = match ($updateType) {
            UpdateType::Major->value => 8,
            UpdateType::Minor->value => 4,
            UpdateType::Patch->value => 1,
            default => 2,
        };

        return $isDirect ? $penalty : (int) ceil($penalty / 2);
    }
}
