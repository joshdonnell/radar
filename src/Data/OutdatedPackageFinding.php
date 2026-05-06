<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\UpdateType;

final readonly class OutdatedPackageFinding
{
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
