<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

use JoshDonnell\Radar\Enums\UpdateType;

trait ClassifiesUpdateTypes
{
    private function classifyUpdateType(string $currentVersion, string $latestVersion): UpdateType
    {
        $current = $this->semanticVersionParts($currentVersion);
        $latest = $this->semanticVersionParts($latestVersion);

        if ($current === null || $latest === null) {
            return UpdateType::Unknown;
        }

        if ($latest[0] > $current[0]) {
            return UpdateType::Major;
        }

        if ($latest[0] === $current[0] && $latest[1] > $current[1]) {
            return UpdateType::Minor;
        }

        if ($latest[0] === $current[0] && $latest[1] === $current[1] && $latest[2] > $current[2]) {
            return UpdateType::Patch;
        }

        return UpdateType::Unknown;
    }

    /** @return array{int, int, int}|null */
    private function semanticVersionParts(string $version): ?array
    {
        if (preg_match('/^v?(\d+)\.(\d+)\.(\d+)/', $version, $matches) !== 1) {
            return null;
        }

        return [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
    }
}
