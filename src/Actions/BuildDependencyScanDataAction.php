<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Data\DependencyScanData;

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

        $composerVulnerabilities = $composerPackages === []
            ? []
            : $this->detectComposerVulnerabilities->execute($basepath, $composerPackages);

        $npmVulnerabilities = $npmPackages === []
            ? []
            : $this->detectNpmVulnerabilities->execute($basepath, $npmPackages);

        $composerOutdated = $composerPackages === []
            ? []
            : $this->detectOutdatedComposerPackages->execute($basepath, $composerPackages);

        $npmOutdated = $npmPackages === []
            ? []
            : $this->detectOutdatedNpmPackages->execute($basepath, $npmPackages);

        $abandoned = $composerPackages === []
            ? []
            : $this->detectAbandonedComposerPackages->execute($basepath, $composerPackages);

        return new DependencyScanData(
            packages: [
                ...$composerPackages,
                ...$npmPackages,
            ],
            vulnerabilities: [
                ...$composerVulnerabilities,
                ...$npmVulnerabilities,
            ],
            outdated: [
                ...$composerOutdated,
                ...$npmOutdated,
            ],
            abandoned: $abandoned,
        );
    }
}
