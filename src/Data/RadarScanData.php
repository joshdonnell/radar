<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Models\RadarScan;

final readonly class RadarScanData
{
    public function __construct(
        private RadarScan $scan,
    ) {}

    public static function fromModel(RadarScan $model): self
    {
        return new self($model);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->scan->id,
            'score' => $this->scan->score,
            'package_count' => $this->scan->package_count,
            'vulnerability_count' => $this->scan->vulnerability_count,
            'packages' => array_map(static fn (PackageData $package): array => $package->toArray(), $this->scan->packages()),
            'vulnerabilities' => array_map(static fn (VulnerabilityFindingData $finding): array => $finding->toArray(), $this->scan->vulnerabilities()),
            'outdated' => array_map(static fn (OutdatedPackageFindingData $finding): array => $finding->toArray(), $this->scan->outdated()),
            'abandoned' => array_map(static fn (AbandonedPackageFindingData $finding): array => $finding->toArray(), $this->scan->abandoned()),
            'warnings' => array_map(static fn (ScanWarningData $warning): array => $warning->toArray(), $this->scan->warnings()),
            'created_at' => $this->scan->created_at?->toIso8601String(),
        ];
    }
}
