<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use JoshDonnell\Radar\Enums\VulnerabilitySeverity;
use JoshDonnell\Radar\Models\RadarScan;

final readonly class CiScanReport
{
    private string $severityThreshold;

    private int $vulnerabilityCount;

    /** @var list<string> */
    private array $vulnerabilityLines;

    private int $failingVulnerabilityCount;

    public function __construct(RadarScan $scan, VulnerabilitySeverity $severityThreshold)
    {
        $vulnerabilities = $this->payloadList($scan, 'vulnerabilities');

        $this->severityThreshold = $severityThreshold->value;
        $this->vulnerabilityCount = count($vulnerabilities);
        $this->vulnerabilityLines = array_map(
            fn (array $vulnerability): string => $this->vulnerabilityLine($vulnerability, $severityThreshold),
            $vulnerabilities,
        );
        $this->failingVulnerabilityCount = collect($vulnerabilities)
            ->filter(fn (array $vulnerability): bool => $this->vulnerabilityFailsThreshold($vulnerability, $severityThreshold))
            ->count();
    }

    public function exitCode(): int
    {
        return $this->failingVulnerabilityCount > 0 ? 1 : 0;
    }

    /** @return list<string> */
    public function lines(): array
    {
        $lines = [
            sprintf(
                'Radar scan completed with %d vulnerability finding(s).',
                $this->vulnerabilityCount,
            ),
            sprintf(
                'CI severity threshold: %s. Failing vulnerability finding(s): %d.',
                $this->severityThreshold,
                $this->failingVulnerabilityCount,
            ),
        ];

        if ($this->vulnerabilityCount === 0) {
            return [
                ...$lines,
                'Radar scan passed. No vulnerabilities found.',
            ];
        }

        $lines = [
            ...$lines,
            ...$this->vulnerabilityLines,
        ];

        if ($this->failingVulnerabilityCount === 0) {
            $lines[] = sprintf(
                'Radar scan passed. No vulnerabilities at %s severity or above.',
                $this->severityThreshold,
            );
        }

        return $lines;
    }

    /** @return list<array<string, mixed>> */
    private function payloadList(RadarScan $scan, string $key): array
    {
        $value = $scan->payload[$key] ?? [];

        if (! is_array($value)) {
            return [];
        }

        $items = [];

        foreach ($value as $item) {
            if (! is_array($item)) {
                continue;
            }

            $typedItem = [];

            foreach ($item as $itemKey => $itemValue) {
                if (! is_string($itemKey)) {
                    continue;
                }

                $typedItem[$itemKey] = $itemValue;
            }

            $items[] = $typedItem;
        }

        return $items;
    }

    /** @param array<string, mixed> $vulnerability */
    private function vulnerabilityFailsThreshold(array $vulnerability, VulnerabilitySeverity $severityThreshold): bool
    {
        return $this->severity($vulnerability)->meetsThreshold($severityThreshold);
    }

    /** @param array<string, mixed> $vulnerability */
    private function vulnerabilityLine(array $vulnerability, VulnerabilitySeverity $severityThreshold): string
    {
        $level = $this->vulnerabilityFailsThreshold($vulnerability, $severityThreshold) ? 'ERROR' : 'WARNING';

        return sprintf('[%s] %s', $level, $this->vulnerabilityMessage($vulnerability));
    }

    /** @param array<string, mixed> $vulnerability */
    private function severity(array $vulnerability): VulnerabilitySeverity
    {
        return VulnerabilitySeverity::fromAuditSeverity($this->stringValue($vulnerability, 'severity'));
    }

    /** @param array<string, mixed> $items */
    private function stringValue(array $items, string $key): ?string
    {
        $value = $items[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    /** @param array<string, mixed> $vulnerability */
    private function vulnerabilityMessage(array $vulnerability): string
    {
        $packageName = $this->stringValue($vulnerability, 'package_name') ?? 'unknown package';
        $severity = $this->severity($vulnerability)->value;
        $message = sprintf('%s %s severity vulnerability found', $packageName, $severity);
        $cve = $this->stringValue($vulnerability, 'cve');
        $affectedVersions = $this->stringValue($vulnerability, 'affected_versions');
        $suggestedCommand = $this->stringValue($vulnerability, 'suggested_command');

        if ($cve !== null) {
            $message = sprintf('%s. CVE: %s', $message, $cve);
        }

        if ($affectedVersions !== null) {
            $message = sprintf('%s. Affected versions: %s', $message, $affectedVersions);
        }

        if ($suggestedCommand !== null) {
            return sprintf('%s. Suggested command: %s', $message, $suggestedCommand);
        }

        return $message;
    }
}
