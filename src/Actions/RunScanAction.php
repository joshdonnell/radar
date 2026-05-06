<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Data\AbandonedPackageFindingData;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Models\RadarScan;

final readonly class RunScanAction
{
    public function __construct(
        private ParseComposerPackagesAction $parseComposerPackages,
        private ParseNpmPackagesAction $parseNpmPackages,
        private DetectAbandonedComposerPackagesAction $detectAbandonedComposerPackages,
        private DetectOutdatedComposerPackagesAction $detectOutdatedComposerPackages,
        private DetectOutdatedNpmPackagesAction $detectOutdatedNpmPackages,
        private DetectComposerVulnerabilitiesAction $detectComposerVulnerabilities,
        private DetectNpmVulnerabilitiesAction $detectNpmVulnerabilities,
        private CreateScanResultAction $createScanResult,
        private CalculateHealthScoreAction $calculateHealthScore,
    ) {}

    public function execute(?string $basepath = null): RadarScan
    {
        $basepath ??= base_path();

        $packages = [
            ...$this->parseComposerPackages->execute($basepath),
            ...$this->parseNpmPackages->execute($basepath),
        ];

        $abandoned = $this->detectAbandonedComposerPackages->execute($basepath);
        $outdated = [
            ...$this->detectOutdatedComposerPackages->execute($basepath),
            ...$this->detectOutdatedNpmPackages->execute($basepath),
        ];
        $vulnerabilities = [
            ...$this->detectComposerVulnerabilities->execute($basepath),
            ...$this->detectNpmVulnerabilities->execute($basepath),
        ];

        $payload = [
            'packages' => array_map(
                static fn (PackageData $package): array => $package->toArray(),
                $packages,
            ),
            'vulnerabilities' => array_map(
                static fn (VulnerabilityFindingData $finding): array => $finding->toArray(),
                $vulnerabilities,
            ),
            'outdated' => array_map(
                static fn (OutdatedPackageFindingData $finding): array => $finding->toArray(),
                $outdated,
            ),
            'abandoned' => array_map(
                static fn (AbandonedPackageFindingData $finding): array => $finding->toArray(),
                $abandoned,
            ),
        ];

        $scan = $this->createScanResult->execute(
            payload: $payload,
            score: $this->calculateHealthScore->execute(
                vulnerabilities: $vulnerabilities,
                outdatedPackages: $outdated,
                abandonedPackages: $abandoned,
            ),
            vulnerabilityCount: count($vulnerabilities),
            packageCount: count($packages),
        );

        (new RadarScan())->pruneAll();

        return $scan;
    }
}
