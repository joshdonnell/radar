<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Data\DependencyScanData;
use JoshDonnell\Radar\Data\DetectionResultData;
use JoshDonnell\Radar\Data\ScanWarningData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;
use JoshDonnell\Radar\Enums\ScanCheck;

final readonly class BuildDependencyScanDataAction
{
    public function __construct(
        private ParseComposerPackagesAction $parseComposerPackages,
        private ParseNpmPackagesAction $parseNpmPackages,
        private DetectAbandonedComposerPackagesAction $detectAbandonedComposerPackages,
        private DetectOutdatedComposerPackagesAction $detectOutdatedComposerPackages,
        private DetectOutdatedNpmPackagesAction $detectOutdatedNpmPackages,
        private DetectComposerVulnerabilitiesAction $detectComposerVulnerabilities,
        private DetectNpmVulnerabilitiesAction $detectNpmVulnerabilities,
    ) {}

    public function execute(?string $basepath = null): DependencyScanData
    {
        $basepath ??= base_path();

        $composerPackages = $this->parseComposerPackages->execute($basepath);
        $npmPackages = $this->parseNpmPackages->execute($basepath);

        if ($composerPackages !== []) {
            $this->detectComposerVulnerabilities->prepare($basepath);
            $this->detectOutdatedComposerPackages->prepare($basepath);
        }

        if ($npmPackages !== []) {
            $this->detectNpmVulnerabilities->prepare($basepath);
            $this->detectOutdatedNpmPackages->prepare($basepath);
        }

        $composerVulnerabilities = $composerPackages === []
            ? new DetectionResultData()
            : $this->detectComposerVulnerabilities->execute($basepath, $composerPackages);

        $composerOutdated = $composerPackages === []
            ? new DetectionResultData()
            : $this->detectOutdatedComposerPackages->execute($basepath, $composerPackages);

        $npmVulnerabilities = $npmPackages === []
            ? new DetectionResultData()
            : $this->detectNpmVulnerabilities->execute($basepath, $npmPackages);

        $npmOutdated = $npmPackages === []
            ? new DetectionResultData()
            : $this->detectOutdatedNpmPackages->execute($basepath, $npmPackages);

        return new DependencyScanData(
            packages: [
                ...$composerPackages,
                ...$npmPackages,
            ],
            vulnerabilities: [
                ...$composerVulnerabilities->findings,
                ...$npmVulnerabilities->findings,
            ],
            outdated: [
                ...$composerOutdated->findings,
                ...$npmOutdated->findings,
            ],
            abandoned: $composerPackages === []
                ? []
                : $this->detectAbandonedComposerPackages->execute($basepath, $composerPackages),
            warnings: [
                ...$this->inventoryWarnings($basepath, $npmPackages !== []),
                ...$composerVulnerabilities->warnings,
                ...$npmVulnerabilities->warnings,
                ...$composerOutdated->warnings,
                ...$npmOutdated->warnings,
            ],
        );
    }

    /** @return list<ScanWarningData> */
    private function inventoryWarnings(string $basepath, bool $hasNpmPackages): array
    {
        if (! $hasNpmPackages) {
            return [];
        }

        if (file_exists("{$basepath}/package-lock.json")) {
            return [];
        }

        $nodeRunner = NodeRunner::fromProjectPath($basepath);

        if ($nodeRunner === NodeRunner::Npm) {
            return [];
        }

        return [
            new ScanWarningData(
                ecosystem: Ecosystem::Npm,
                check: ScanCheck::Inventory,
                message: sprintf(
                    'Radar reads the full Node package tree from package-lock.json only. For this %s project, only direct dependencies are listed.',
                    ucfirst($nodeRunner->value),
                ),
            ),
        ];
    }
}
