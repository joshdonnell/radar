<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class AbandonedPackageFindingData
{
    public function __construct(
        public string $id,
        public Ecosystem $ecosystem,
        public string $packageName,
        public string $installedVersion,
        public DependencyType $dependencyType,
        public bool $isDirect,
        public ?string $replacementPackage = null,
        public ?string $recommendation = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ecosystem' => $this->ecosystem->value,
            'package_name' => $this->packageName,
            'installed_version' => $this->installedVersion,
            'dependency_type' => $this->dependencyType->value,
            'is_direct' => $this->isDirect,
            'replacement_package' => $this->replacementPackage,
            'recommendation' => $this->recommendation,
        ];
    }
}
