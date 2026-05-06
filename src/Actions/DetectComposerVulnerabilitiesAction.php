<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Concerns\RunsReadOnlyCommands;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;

final readonly class DetectComposerVulnerabilitiesAction
{
    use ReadsJsonFiles;
    use RunsReadOnlyCommands;

    public function __construct(
        private ParseComposerPackagesAction $parseComposerPackages,
        private BuildSafeRecommendationAction $buildSafeRecommendation,
    ) {}

    /** @return list<VulnerabilityFindingData> */
    public function execute(?string $basepath = null): array
    {
        $basepath ??= base_path();

        $auditReport = $this->readJson($basepath.'/composer-audit.json');

        if ($auditReport === []) {
            $auditReport = $this->readCommandJson(['composer', 'audit', '--format=json'], $basepath);
        }

        $packages = $this->packagesByName($this->parseComposerPackages->execute($basepath));
        $findings = [];

        foreach ($this->advisories($auditReport) as $packageName => $advisories) {
            $package = $packages[$packageName] ?? null;

            if (! $package instanceof PackageData) {
                continue;
            }

            foreach ($advisories as $advisory) {
                $advisoryId = $this->stringValue($advisory, 'advisoryId')
                    ?? $this->stringValue($advisory, 'advisory_id')
                    ?? $this->stringValue($advisory, 'id')
                    ?? 'composer-'.$packageName.'-advisory';

                $findings[] = new VulnerabilityFindingData(
                    id: $advisoryId,
                    ecosystem: Ecosystem::Composer,
                    packageName: $packageName,
                    installedVersion: $package->installedVersion,
                    severity: VulnerabilitySeverity::fromAuditSeverity($this->stringValue($advisory, 'severity')),
                    advisoryId: $advisoryId,
                    isDirect: $package->isDirect === true,
                    cve: $this->stringValue($advisory, 'cve'),
                    affectedVersions: $this->stringValue($advisory, 'affectedVersions') ?? $this->stringValue($advisory, 'affected_versions'),
                    patchedVersion: $this->stringValue($advisory, 'patchedVersion') ?? $this->stringValue($advisory, 'patched_version'),
                    advisoryUrl: $this->stringValue($advisory, 'link') ?? $this->stringValue($advisory, 'url'),
                    recommendation: $this->buildSafeRecommendation->forVulnerabilityFields(
                        isDirect: $package->isDirect === true,
                        packageName: $packageName,
                    ),
                    suggestedCommand: $this->buildSafeRecommendation->commandForVulnerabilityFields(
                        isDirect: $package->isDirect === true,
                        packageName: $packageName,
                        ecosystem: Ecosystem::Composer,
                    ),
                    requiredBy: $package->requiredBy,
                );
            }
        }

        return $findings;
    }

    /**
     * @param  array<string, mixed>  $auditReport
     * @return array<string, list<array<string, mixed>>>
     */
    private function advisories(array $auditReport): array
    {
        $advisories = $auditReport['advisories'] ?? [];

        if (! is_array($advisories)) {
            return [];
        }

        /** @var array<string, list<array<string, mixed>>> $advisories */
        return $advisories;
    }
}
