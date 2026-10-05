<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Concerns\ReadsArrayValues;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\ScanCheck;

final readonly class ScanWarningData
{
    use ReadsArrayValues;

    public function __construct(
        public Ecosystem $ecosystem,
        public ScanCheck $check,
        public string $message,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            ecosystem: Ecosystem::tryFrom(self::stringValue($data, 'ecosystem') ?? '') ?? Ecosystem::Composer,
            check: ScanCheck::tryFrom(self::stringValue($data, 'check') ?? '') ?? ScanCheck::Vulnerabilities,
            message: self::stringValue($data, 'message') ?? 'Unknown scan warning.',
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'ecosystem' => $this->ecosystem->value,
            'check' => $this->check->value,
            'message' => $this->message,
        ];
    }
}
