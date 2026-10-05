<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Concerns\ReadsArrayValues;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\UpdateType;

final readonly class OutdatedPackageFindingData
{
    use ReadsArrayValues;

    public function __construct(
        public string $id,
        public Ecosystem $ecosystem,
        public string $packageName,
        public string $currentVersion,
        public string $latestVersion,
        public UpdateType $updateType,
        public DependencyType $dependencyType,
        public bool $isDirect,
        public ?string $suggestedCommand = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::stringValue($data, 'id') ?? 'unknown-outdated',
            ecosystem: Ecosystem::tryFrom(self::stringValue($data, 'ecosystem') ?? '') ?? Ecosystem::Composer,
            packageName: self::stringValue($data, 'package_name') ?? 'unknown/package',
            currentVersion: self::stringValue($data, 'current_version') ?? 'unknown',
            latestVersion: self::stringValue($data, 'latest_version') ?? 'unknown',
            updateType: UpdateType::tryFrom(self::stringValue($data, 'update_type') ?? '') ?? UpdateType::Unknown,
            dependencyType: DependencyType::tryFrom(self::stringValue($data, 'dependency_type') ?? '') ?? DependencyType::Production,
            isDirect: self::boolValue($data, 'is_direct'),
            suggestedCommand: self::stringValue($data, 'suggested_command'),
        );
    }

    public function withSuggestedCommand(?string $suggestedCommand): self
    {
        return new self(
            id: $this->id,
            ecosystem: $this->ecosystem,
            packageName: $this->packageName,
            currentVersion: $this->currentVersion,
            latestVersion: $this->latestVersion,
            updateType: $this->updateType,
            dependencyType: $this->dependencyType,
            isDirect: $this->isDirect,
            suggestedCommand: $suggestedCommand,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ecosystem' => $this->ecosystem->value,
            'package_name' => $this->packageName,
            'current_version' => $this->currentVersion,
            'latest_version' => $this->latestVersion,
            'update_type' => $this->updateType->value,
            'dependency_type' => $this->dependencyType->value,
            'is_direct' => $this->isDirect,
            'suggested_command' => $this->suggestedCommand,
        ];
    }
}
