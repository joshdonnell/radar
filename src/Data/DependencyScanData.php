<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

final readonly class DependencyScanData
{
    /**
     * @param  list<PackageData>  $packages
     * @param  list<VulnerabilityFindingData>  $vulnerabilities
     * @param  list<OutdatedPackageFindingData>  $outdated
     * @param  list<AbandonedPackageFindingData>  $abandoned
     * @param  list<ScanWarningData>  $warnings
     */
    public function __construct(
        public array $packages = [],
        public array $vulnerabilities = [],
        public array $outdated = [],
        public array $abandoned = [],
        public array $warnings = [],
    ) {}
}
