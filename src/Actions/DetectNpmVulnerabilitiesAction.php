<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Actions;

use JoshDonnell\Radar\Concerns\ReadsJsonFiles;
use JoshDonnell\Radar\Concerns\RunsReadOnlyCommands;
use JoshDonnell\Radar\Data\PackageData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;

final readonly class DetectNpmVulnerabilitiesAction
{
    use ReadsJsonFiles;
    use RunsReadOnlyCommands;

    public function __construct(
        private ParseNpmPackagesAction $parseNpmPackages,
        private BuildSafeRecommendationAction $buildSafeRecommendation,
    ) {}

    /** @return list<VulnerabilityFindingData> */
    public function execute(?string $basepath = null): array
    {
        $basepath ??= base_path();

        $auditReport = $this->readJson($basepath.'/npm-audit.json');
        $nodeRunner = NodeRunner::fromProjectPath($basepath);

        if ($auditReport === []) {
            $auditReport = match ($nodeRunner) {
                NodeRunner::Npm => $this->readCommandJson(['npm', 'audit', '--json'], $basepath),
                NodeRunner::Pnpm => $this->readCommandJson(['pnpm', 'audit', '--json'], $basepath),
                NodeRunner::Yarn => $this->readYarnAuditJson($basepath),
                NodeRunner::Bun => [],
            };
        }

        $packages = $this->npmPackagesByName($this->parseNpmPackages->execute($basepath));
        $findings = [];

        foreach ($this->vulnerabilities($auditReport) as $packageName => $vulnerability) {
            $package = $this->packageForVulnerability($packages[$packageName] ?? [], $vulnerability);

            if (! $package instanceof PackageData) {
                continue;
            }

            /** @var array<string, mixed> $advisory */
            $advisory = $this->firstAdvisory($vulnerability);
            $advisoryId = $this->advisoryId($advisory, $packageName);

            $findings[] = new VulnerabilityFindingData(
                id: $advisoryId,
                ecosystem: Ecosystem::Npm,
                packageName: $packageName,
                installedVersion: $package->installedVersion,
                severity: VulnerabilitySeverity::fromAuditSeverity($this->stringValue($advisory, 'severity') ?? $this->stringValue($vulnerability, 'severity')),
                advisoryId: $advisoryId,
                isDirect: $package->isDirect === true,
                affectedVersions: $this->stringValue($advisory, 'range') ?? $this->stringValue($vulnerability, 'range'),
                advisoryUrl: $this->stringValue($advisory, 'url'),
                recommendation: $this->buildSafeRecommendation->forVulnerabilityFields(
                    isDirect: $package->isDirect === true,
                    packageName: $packageName,
                ),
                suggestedCommand: $this->buildSafeRecommendation->commandForVulnerabilityFields(
                    isDirect: $package->isDirect === true,
                    packageName: $packageName,
                    ecosystem: Ecosystem::Npm,
                    nodeRunner: $nodeRunner,
                ),
                requiredBy: $package->requiredBy,
            );
        }

        return $findings;
    }

    /**
     * @param  list<PackageData>  $packages
     * @return array<string, list<PackageData>>
     */
    private function npmPackagesByName(array $packages): array
    {
        $mapped = [];

        foreach ($packages as $package) {
            $mapped[$package->name] ??= [];
            $mapped[$package->name][] = $package;
        }

        return $mapped;
    }

    /**
     * @param  list<PackageData>  $packages
     * @param  array<string, mixed>  $vulnerability
     */
    private function packageForVulnerability(array $packages, array $vulnerability): ?PackageData
    {
        foreach ($this->vulnerableNodes($vulnerability) as $node) {
            foreach ($packages as $package) {
                if ($package->path === $node) {
                    return $package;
                }
            }
        }

        foreach ($packages as $package) {
            if ($package->isDirect === true) {
                return $package;
            }
        }

        return $packages[0] ?? null;
    }

    /**
     * @param  array<string, mixed>  $vulnerability
     * @return list<string>
     */
    private function vulnerableNodes(array $vulnerability): array
    {
        $nodes = $vulnerability['nodes'] ?? [];

        if (! is_array($nodes)) {
            return [];
        }

        $vulnerableNodes = [];

        foreach ($nodes as $node) {
            if (is_string($node)) {
                $vulnerableNodes[] = $node;
            }
        }

        return $vulnerableNodes;
    }

    /**
     * Yarn audit outputs line-delimited JSON. Each line is a separate JSON
     * object. We need to collect all auditAdvisory lines and build a report.
     *
     * @return array<string, mixed>
     */
    private function readYarnAuditJson(string $basepath): array
    {
        $output = $this->readCommandOutput(['yarn', 'audit', '--json'], $basepath);

        if ($output === null) {
            return [];
        }

        $vulnerabilities = [];

        foreach (explode("\n", $output) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $decoded = json_decode($line, true);

            if (! is_array($decoded)) {
                continue;
            }

            if (($decoded['type'] ?? null) !== 'auditAdvisory') {
                continue;
            }

            $data = $decoded['data'] ?? null;

            if (! is_array($data)) {
                continue;
            }

            $advisory = $data['advisory'] ?? null;

            if (! is_array($advisory)) {
                continue;
            }

            $moduleName = $advisory['module_name'] ?? null;

            if (! is_string($moduleName)) {
                continue;
            }

            $vulnerabilities[$moduleName] = [
                'severity' => $advisory['severity'] ?? 'unknown',
                'via' => [
                    [
                        'source' => $advisory['id'] ?? null,
                        'name' => $moduleName,
                        'dependency' => $moduleName,
                        'title' => $advisory['title'] ?? null,
                        'url' => $advisory['url'] ?? null,
                        'severity' => $advisory['severity'] ?? 'unknown',
                        'range' => $advisory['vulnerable_versions'] ?? null,
                    ],
                ],
            ];
        }

        if ($vulnerabilities === []) {
            return [];
        }

        return ['vulnerabilities' => $vulnerabilities];
    }

    /**
     * Supports both npm v7+ (vulnerabilities key) and npm v6/pnpm
     * (advisories key) formats.
     *
     * @param  array<string, mixed>  $auditReport
     * @return array<string, array<string, mixed>>
     */
    private function vulnerabilities(array $auditReport): array
    {
        // npm v7+ format
        $vulnerabilities = $auditReport['vulnerabilities'] ?? [];

        if (is_array($vulnerabilities) && $vulnerabilities !== []) {
            /** @var array<string, array<string, mixed>> $vulnerabilities */
            return $vulnerabilities;
        }

        // npm v6 / pnpm format
        $advisories = $auditReport['advisories'] ?? [];

        if (! is_array($advisories)) {
            return [];
        }

        $normalized = [];

        foreach ($advisories as $advisoryId => $advisory) {
            if (! is_array($advisory)) {
                continue;
            }

            $moduleName = $advisory['module_name'] ?? null;

            if (! is_string($moduleName)) {
                continue;
            }

            $normalized[$moduleName] = [
                'severity' => $advisory['severity'] ?? 'unknown',
                'via' => [
                    [
                        'source' => $advisoryId,
                        'name' => $moduleName,
                        'dependency' => $moduleName,
                        'title' => $advisory['title'] ?? null,
                        'url' => $advisory['url'] ?? null,
                        'severity' => $advisory['severity'] ?? 'unknown',
                        'range' => $advisory['vulnerable_versions'] ?? null,
                    ],
                ],
            ];
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $vulnerability
     * @return array<string, mixed>
     */
    private function firstAdvisory(array $vulnerability): array
    {
        $via = $vulnerability['via'] ?? [];

        if (! is_array($via)) {
            return [];
        }

        foreach ($via as $advisory) {
            if (is_array($advisory)) {
                /** @var array<string, mixed> $advisory */
                return $advisory;
            }
        }

        return [];
    }

    /** @param array<string, mixed> $advisory */
    private function advisoryId(array $advisory, string $packageName): string
    {
        $source = $advisory['source'] ?? null;

        if (is_int($source) || is_string($source)) {
            return 'npm-'.$packageName.'-'.$source;
        }

        return 'npm-'.$packageName.'-advisory';
    }
}
