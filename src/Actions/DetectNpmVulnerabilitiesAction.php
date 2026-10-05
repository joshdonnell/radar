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
use JoshDonnell\Radar\Enums\NodeRunner;
use JoshDonnell\Radar\Enums\ScanCheck;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Exceptions\CommandFailedException;
use JoshDonnell\Radar\Support\ReadOnlyCommandRunner;

final readonly class DetectNpmVulnerabilitiesAction
{
    use IndexesPackages;
    use ReadsArrayValues;
    use ReadsJsonFiles;

    public function __construct(
        private BuildSafeRecommendationAction $buildSafeRecommendation,
        private ReadOnlyCommandRunner $commandRunner,
    ) {}

    public function prepare(string $basepath): void
    {
        if ($this->hasReportFile($basepath)) {
            return;
        }

        $command = $this->command(NodeRunner::fromProjectPath($basepath));

        if ($command === null) {
            return;
        }

        $this->commandRunner->start($command, $basepath);
    }

    /**
     * @param  list<PackageData>  $packages
     * @return DetectionResultData<VulnerabilityFindingData>
     */
    public function execute(string $basepath, array $packages): DetectionResultData
    {
        $nodeRunner = NodeRunner::fromProjectPath($basepath);

        try {
            $auditReport = $this->auditReport($basepath, $nodeRunner);
        } catch (CommandFailedException $commandFailedException) {
            return DetectionResultData::failed(Ecosystem::Npm, ScanCheck::Vulnerabilities, $commandFailedException->getMessage());
        }

        if ($auditReport === null) {
            return DetectionResultData::failed(
                Ecosystem::Npm,
                ScanCheck::Vulnerabilities,
                sprintf('Radar cannot audit %s projects yet, so Node packages were not checked for vulnerabilities.', ucfirst($nodeRunner->value)),
            );
        }

        $packagesByName = $this->packagesGroupedByName($packages);
        $findings = [];

        foreach ($this->vulnerabilities($auditReport) as $packageName => $vulnerability) {
            $package = $this->packageForVulnerability($packagesByName[$packageName] ?? [], $vulnerability);
            $isDirect = $package?->isDirect === true;
            $requiredBy = $package->requiredBy ?? [];

            /** @var array<string, mixed> $advisory */
            $advisory = $this->firstAdvisory($vulnerability);
            $advisoryId = $this->advisoryId($advisory, $packageName);

            $findings[] = new VulnerabilityFindingData(
                id: $advisoryId,
                ecosystem: Ecosystem::Npm,
                packageName: $packageName,
                installedVersion: $package->installedVersion
                    ?? self::stringValue($vulnerability, 'installed_version')
                    ?? 'unknown',
                severity: VulnerabilitySeverity::fromAuditSeverity(self::stringValue($advisory, 'severity') ?? self::stringValue($vulnerability, 'severity')),
                advisoryId: $advisoryId,
                isDirect: $isDirect,
                title: self::stringValue($advisory, 'title'),
                affectedVersions: self::stringValue($advisory, 'range') ?? self::stringValue($vulnerability, 'range'),
                advisoryUrl: self::stringValue($advisory, 'url'),
                recommendation: $this->buildSafeRecommendation->forVulnerability($isDirect, $packageName),
                suggestedCommand: $this->buildSafeRecommendation->commandForVulnerability(
                    ecosystem: Ecosystem::Npm,
                    packageName: $packageName,
                    isDirect: $isDirect,
                    requiredBy: $requiredBy,
                    nodeRunner: $nodeRunner,
                ),
                alternativeCommands: $this->buildSafeRecommendation->alternativeCommandsForVulnerability(
                    ecosystem: Ecosystem::Npm,
                    packageName: $packageName,
                    isDirect: $isDirect,
                    requiredBy: $requiredBy,
                    nodeRunner: $nodeRunner,
                ),
                requiredBy: $requiredBy,
            );
        }

        return new DetectionResultData($findings);
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws CommandFailedException
     */
    private function auditReport(string $basepath, NodeRunner $nodeRunner): ?array
    {
        if ($this->hasReportFile($basepath)) {
            return $this->readJson($this->reportFile($basepath));
        }

        $command = $this->command($nodeRunner);

        if ($command === null) {
            return null;
        }

        if ($nodeRunner === NodeRunner::Yarn) {
            return $this->yarnAuditReport($this->commandRunner->output($command, $basepath));
        }

        return $this->commandRunner->json($command, $basepath);
    }

    /** @return list<string>|null */
    private function command(NodeRunner $nodeRunner): ?array
    {
        return match ($nodeRunner) {
            NodeRunner::Npm => ['npm', 'audit', '--json'],
            NodeRunner::Pnpm => ['pnpm', 'audit', '--json'],
            NodeRunner::Yarn => ['yarn', 'audit', '--json'],
            NodeRunner::Bun => null,
        };
    }

    private function hasReportFile(string $basepath): bool
    {
        return file_exists($this->reportFile($basepath));
    }

    private function reportFile(string $basepath): string
    {
        return "{$basepath}/npm-audit.json";
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
        return self::stringListValue($vulnerability, 'nodes');
    }

    /**
     * Yarn prints one JSON document per line rather than a single report.
     *
     * @return array<string, mixed>
     */
    private function yarnAuditReport(string $output): array
    {
        $advisories = [];

        foreach (explode("\n", $output) as $line) {
            $decoded = json_decode(mb_trim($line), true);

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

            $advisories[] = $advisory;
        }

        return ['advisories' => $advisories];
    }

    /**
     * Normalises both the npm 7+ `vulnerabilities` report and the legacy
     * `advisories` report (npm 6, pnpm and yarn) to the npm 7+ shape.
     *
     * @param  array<string, mixed>  $auditReport
     * @return array<string, array<string, mixed>>
     */
    private function vulnerabilities(array $auditReport): array
    {
        $vulnerabilities = $auditReport['vulnerabilities'] ?? [];

        if (is_array($vulnerabilities) && $vulnerabilities !== []) {
            /** @var array<string, array<string, mixed>> $vulnerabilities */
            return $vulnerabilities;
        }

        $normalized = [];

        foreach ($this->arrayValue($auditReport, 'advisories') as $advisory) {
            if (! is_array($advisory)) {
                continue;
            }

            $moduleName = self::stringValue($advisory, 'module_name');

            if ($moduleName === null) {
                continue;
            }

            $findings = self::recordListValue($advisory, 'findings');

            $normalized[$moduleName] = [
                'severity' => $advisory['severity'] ?? 'unknown',
                'installed_version' => self::stringValue($findings[0] ?? [], 'version'),
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

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<array-key, mixed>
     */
    private function arrayValue(array $values, string $key): array
    {
        $value = $values[$key] ?? [];

        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $vulnerability
     * @return array<string, mixed>
     */
    private function firstAdvisory(array $vulnerability): array
    {
        foreach (self::recordListValue($vulnerability, 'via') as $advisory) {
            return $advisory;
        }

        return [];
    }

    /** @param array<string, mixed> $advisory */
    private function advisoryId(array $advisory, string $packageName): string
    {
        $source = $advisory['source'] ?? null;

        if (is_int($source) || is_string($source)) {
            return "npm-{$packageName}-{$source}";
        }

        return "npm-{$packageName}-advisory";
    }
}
