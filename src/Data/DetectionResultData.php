<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Data;

use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\ScanCheck;

/**
 * @template TFinding
 */
final readonly class DetectionResultData
{
    /**
     * @param  list<TFinding>  $findings
     * @param  list<ScanWarningData>  $warnings
     */
    public function __construct(
        public array $findings = [],
        public array $warnings = [],
    ) {}

    /** @return self<never> */
    public static function failed(Ecosystem $ecosystem, ScanCheck $check, string $message): self
    {
        return new self(warnings: [new ScanWarningData($ecosystem, $check, $message)]);
    }
}
