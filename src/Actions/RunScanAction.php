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
        private BuildDependencyScanDataAction $buildDependencyScanData,
        private CreateScanResultAction $createScanResult,
        private CalculateHealthScoreAction $calculateHealthScore,
    ) {}

    public function execute(?string $basepath = null): RadarScan
    {
        $basepath ??= base_path();

        $dependencyScan = $this->buildDependencyScanData->execute($basepath);

        $payload = [
            'packages' => array_map(
                static fn (PackageData $package): array => $package->toArray(),
                $dependencyScan->packages,
            ),
            'vulnerabilities' => array_map(
                static fn (VulnerabilityFindingData $finding): array => $finding->toArray(),
                $dependencyScan->vulnerabilities,
            ),
            'outdated' => array_map(
                static fn (OutdatedPackageFindingData $finding): array => $finding->toArray(),
                $dependencyScan->outdated,
            ),
            'abandoned' => array_map(
                static fn (AbandonedPackageFindingData $finding): array => $finding->toArray(),
                $dependencyScan->abandoned,
            ),
        ];

        $scan = $this->createScanResult->execute(
            payload: $payload,
            score: $this->calculateHealthScore->execute(
                vulnerabilities: $dependencyScan->vulnerabilities,
                outdatedPackages: $dependencyScan->outdated,
                abandonedPackages: $dependencyScan->abandoned,
            ),
            vulnerabilityCount: count($dependencyScan->vulnerabilities),
            packageCount: count($dependencyScan->packages),
        );

        (new RadarScan())->pruneAll();

        return $scan;
    }
}
