<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use Illuminate\Console\Command;
use JoshDonnell\Radar\Data\ScanWarningData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\ScanCheck;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Models\RadarScan;

final readonly class CiScanReport
{
    /** @var list<VulnerabilityFindingData> */
    private array $vulnerabilities;

    /** @var list<VulnerabilityFindingData> */
    private array $failingVulnerabilities;

    /** @var list<ScanWarningData> */
    private array $auditWarnings;

    public function __construct(RadarScan $scan, private VulnerabilitySeverity $severityThreshold)
    {
        $this->vulnerabilities = $scan->vulnerabilities();
        $this->failingVulnerabilities = array_values(array_filter(
            $this->vulnerabilities,
            $this->fails(...),
        ));
        $this->auditWarnings = $scan->warningsFor(ScanCheck::Vulnerabilities);
    }

    /**
     * A failing vulnerability fails the build. An audit that could not run is
     * reported as invalid rather than passing, because a clean result cannot
     * be trusted.
     */
    public function exitCode(): int
    {
        if ($this->failingVulnerabilities !== []) {
            return Command::FAILURE;
        }

        if ($this->auditWarnings !== []) {
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }

    /** @return list<string> */
    public function lines(): array
    {
        $lines = [
            sprintf('Radar scan completed with %d vulnerability finding(s).', count($this->vulnerabilities)),
            sprintf(
                'CI severity threshold: %s. Failing vulnerability finding(s): %d.',
                $this->severityThreshold->value,
                count($this->failingVulnerabilities),
            ),
            ...array_map(
                static fn (ScanWarningData $warning): string => sprintf('[INCOMPLETE] %s audit: %s', $warning->ecosystem->value, $warning->message),
                $this->auditWarnings,
            ),
            ...array_map(
                $this->vulnerabilityLine(...),
                $this->vulnerabilities,
            ),
        ];

        if ($this->failingVulnerabilities !== []) {
            return $lines;
        }

        if ($this->auditWarnings !== []) {
            return [...$lines, 'Radar scan incomplete. One or more dependency audits could not run.'];
        }

        if ($this->vulnerabilities === []) {
            return [...$lines, 'Radar scan passed. No vulnerabilities found.'];
        }

        return [
            ...$lines,
            sprintf('Radar scan passed. No vulnerabilities at %s severity or above.', $this->severityThreshold->value),
        ];
    }

    private function fails(VulnerabilityFindingData $vulnerability): bool
    {
        return $vulnerability->severity->meetsThreshold($this->severityThreshold);
    }

    private function vulnerabilityLine(VulnerabilityFindingData $vulnerability): string
    {
        $level = $this->fails($vulnerability) ? 'ERROR' : 'WARNING';

        $message = sprintf(
            '%s %s severity vulnerability found',
            $vulnerability->packageName,
            $vulnerability->severity->value,
        );

        if ($vulnerability->title !== null) {
            $message .= ": {$vulnerability->title}";
        }

        if ($vulnerability->cve !== null) {
            $message .= ". CVE: {$vulnerability->cve}";
        }

        if ($vulnerability->affectedVersions !== null) {
            $message .= ". Affected versions: {$vulnerability->affectedVersions}";
        }

        if ($vulnerability->suggestedCommand !== null) {
            $message .= ". Suggested command: {$vulnerability->suggestedCommand}";
        }

        return "[{$level}] {$message}";
    }
}
