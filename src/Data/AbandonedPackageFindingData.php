<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Concerns\ReadsArrayValues;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

final readonly class AbandonedPackageFindingData
{
    use ReadsArrayValues;

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

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: self::stringValue($data, 'id') ?? 'unknown-abandoned',
            ecosystem: Ecosystem::tryFrom(self::stringValue($data, 'ecosystem') ?? '') ?? Ecosystem::Composer,
            packageName: self::stringValue($data, 'package_name') ?? 'unknown/package',
            installedVersion: self::stringValue($data, 'installed_version') ?? 'unknown',
            dependencyType: DependencyType::tryFrom(self::stringValue($data, 'dependency_type') ?? '') ?? DependencyType::Production,
            isDirect: self::boolValue($data, 'is_direct'),
            replacementPackage: self::stringValue($data, 'replacement_package'),
            recommendation: self::stringValue($data, 'recommendation'),
        );
    }

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
