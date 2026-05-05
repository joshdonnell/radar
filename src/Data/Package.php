<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class Package
{
    public function __construct(
        public string $id,
        public Ecosystem $ecosystem,
        public string $name,
        public string $installedVersion,
        public DependencyType $dependencyType,
        public ?bool $isDirect = false,
        public ?string $sourceUrl = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ecosystem' => $this->ecosystem->value,
            'name' => $this->name,
            'installed_version' => $this->installedVersion,
            'dependency_type' => $this->dependencyType->value,
            'is_direct' => $this->isDirect,
            'source_url' => $this->sourceUrl,
        ];
    }
}
