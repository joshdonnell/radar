<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use Carbon\CarbonImmutable;

final readonly class RadarScan
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function __construct(
        public string $id,
        public ?int $score,
        public int $package_count,
        public int $vulnerability_count,
        public ?array $payload,
        public ?CarbonImmutable $created_at,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score ?? 100,
            'package_count' => $this->package_count,
            'vulnerability_count' => $this->vulnerability_count,
            'packages' => $this->payload['packages'] ?? [],
            'vulnerabilities' => $this->payload['vulnerabilities'] ?? [],
            'outdated' => $this->payload['outdated'] ?? [],
            'abandoned' => $this->payload['abandoned'] ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}
