<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Concerns\ReadsArrayValues;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class PackageData
{
    use ReadsArrayValues;

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

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::stringValue($data, 'id') ?? 'unknown-package',
            ecosystem: Ecosystem::tryFrom(self::stringValue($data, 'ecosystem') ?? '') ?? Ecosystem::Composer,
            name: self::stringValue($data, 'name') ?? 'unknown/package',
            installedVersion: self::stringValue($data, 'installed_version') ?? 'unknown',
            dependencyType: DependencyType::tryFrom(self::stringValue($data, 'dependency_type') ?? '') ?? DependencyType::Production,
            isDirect: self::boolValue($data, 'is_direct'),
            sourceUrl: self::stringValue($data, 'source_url'),
            requiredBy: self::stringListValue($data, 'required_by'),
        );
    }

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
