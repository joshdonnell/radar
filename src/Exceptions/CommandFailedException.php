<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Exceptions;

use RuntimeException;

final class CommandFailedException extends RuntimeException
{
    /** @param list<string> $command */
    public static function timedOut(array $command, int $timeout): self
    {
        return new self(sprintf('`%s` timed out after %ds.', implode(' ', $command), $timeout));
    }

    /** @param list<string> $command */
    public static function couldNotRun(array $command, string $reason): self
    {
        return new self(sprintf('`%s` could not be run: %s', implode(' ', $command), $reason));
    }

    /** @param list<string> $command */
    public static function invalidOutput(array $command, string $output): self
    {
        $firstLine = mb_trim(strtok($output, "\n") ?: '');

        return new self(sprintf(
            '`%s` did not return valid JSON%s',
            implode(' ', $command),
            $firstLine === '' ? '.' : sprintf(': %s', mb_strimwidth($firstLine, 0, 200, '...')),
        ));
    }
}
