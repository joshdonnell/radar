<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

trait ReadsArrayValues
{
    /** @param array<array-key, mixed> $values */
    private static function stringValue(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /** @param array<array-key, mixed> $values */
    private static function boolValue(array $values, string $key): bool
    {
        return ($values[$key] ?? false) === true;
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return list<string>
     */
    private static function stringListValue(array $values, string $key): array
    {
        $list = $values[$key] ?? [];

        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter($list, is_string(...)));
    }

    /**
     * @param  array<array-key, mixed>  $values
     * @return list<array<string, mixed>>
     */
    private static function recordListValue(array $values, string $key): array
    {
        $list = $values[$key] ?? [];

        if (! is_array($list)) {
            return [];
        }

        $records = [];

        foreach ($list as $record) {
            if (! is_array($record)) {
                continue;
            }

            /** @var array<string, mixed> $record */
            $records[] = $record;
        }

        return $records;
    }
}
