<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\IndexesPackages;
use JoshDonnell\Radar\Concerns\ReadsArrayValues;
use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Data\DetectionResultData;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\ScanCheck;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Exceptions\CommandFailedException;
use JoshDonnell\Radar\Support\ReadOnlyCommandRunner;

final readonly class DetectComposerVulnerabilitiesAction
{
    use IndexesPackages;
    use ReadsArrayValues;
    use ReadsJsonFiles;

    private const array COMMAND = ['composer', 'audit', '--format=json'];

    public function __construct(
        private BuildSafeRecommendationAction $buildSafeRecommendation,
        private ReadOnlyCommandRunner $commandRunner,
    ) {}

    public function prepare(string $basepath): void
    {
        if ($this->hasReportFile($basepath)) {
            return;
        }

        $this->commandRunner->start(self::COMMAND, $basepath);
    }

    /**
     * @param  list<PackageData>  $packages
     * @return DetectionResultData<VulnerabilityFindingData>
     */
    public function execute(string $basepath, array $packages): DetectionResultData
    {
        try {
            $auditReport = $this->hasReportFile($basepath)
                ? $this->readJson($this->reportFile($basepath))
                : $this->commandRunner->json(self::COMMAND, $basepath);
        } catch (CommandFailedException $commandFailedException) {
            return DetectionResultData::failed(Ecosystem::Composer, ScanCheck::Vulnerabilities, $commandFailedException->getMessage());
        }

        $packagesByName = $this->packagesByName($packages);
        $findings = [];

        foreach ($this->advisories($auditReport) as $packageName => $advisories) {
            $package = $packagesByName[$packageName] ?? null;
            $isDirect = $package?->isDirect === true;
            $requiredBy = $package->requiredBy ?? [];

            foreach ($advisories as $advisory) {
                $advisoryId = self::stringValue($advisory, 'advisoryId')
                    ?? self::stringValue($advisory, 'advisory_id')
                    ?? self::stringValue($advisory, 'id')
                    ?? "composer-{$packageName}-advisory";

                $findings[] = new VulnerabilityFindingData(
                    id: $advisoryId,
                    ecosystem: Ecosystem::Composer,
                    packageName: $packageName,
                    installedVersion: $package->installedVersion ?? 'unknown',
                    severity: VulnerabilitySeverity::fromAuditSeverity(self::stringValue($advisory, 'severity')),
                    advisoryId: $advisoryId,
                    isDirect: $isDirect,
                    title: self::stringValue($advisory, 'title'),
                    cve: self::stringValue($advisory, 'cve'),
                    affectedVersions: self::stringValue($advisory, 'affectedVersions') ?? self::stringValue($advisory, 'affected_versions'),
                    patchedVersion: self::stringValue($advisory, 'patchedVersion') ?? self::stringValue($advisory, 'patched_version'),
                    advisoryUrl: self::stringValue($advisory, 'link') ?? self::stringValue($advisory, 'url'),
                    recommendation: $this->buildSafeRecommendation->forVulnerability($isDirect, $packageName),
                    suggestedCommand: $this->buildSafeRecommendation->commandForVulnerability(
                        ecosystem: Ecosystem::Composer,
                        packageName: $packageName,
                        isDirect: $isDirect,
                        requiredBy: $requiredBy,
                    ),
                    alternativeCommands: $this->buildSafeRecommendation->alternativeCommandsForVulnerability(
                        ecosystem: Ecosystem::Composer,
                        packageName: $packageName,
                        isDirect: $isDirect,
                        requiredBy: $requiredBy,
                    ),
                    requiredBy: $requiredBy,
                );
            }
        }

        return new DetectionResultData($findings);
    }

    private function hasReportFile(string $basepath): bool
    {
        return file_exists($this->reportFile($basepath));
    }

    private function reportFile(string $basepath): string
    {
        return "{$basepath}/composer-audit.json";
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
