<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class PackageData
{
    /**
     * @param  list<string>  $requiredBy
     */
    public function __construct(
        public string $id,
        public Ecosystem $ecosystem,
        public string $name,
        public string $installedVersion,
        public DependencyType $dependencyType,
        public ?bool $isDirect = false,
        public ?string $sourceUrl = null,
        public array $requiredBy = [],
        public ?string $path = null,
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
            'required_by' => $this->requiredBy,
        ];
    }
}
