<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

trait RunsReadOnlyCommands
{
    /**
     * @param  list<string>  $command
     * @return array<string, mixed>
     */
    private function readCommandJson(array $command, string $basePath): array
    {
        $contents = $this->readCommandOutput($command, $basePath);

        if ($contents === null) {
            return [];
        }

        $decoded = $this->decodeJsonFromOutput($contents);

        if (! is_array($decoded) || array_is_list($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /** @param list<string> $command */
    private function readCommandOutput(array $command, string $basePath): ?string
    {
        if (! is_dir($basePath)) {
            return null;
        }

        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $basePath);

        if (! is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);

        $timeout = $this->commandTimeout();
        $startedAt = time();

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $errorOutput = '';

        while (true) {
            $status = proc_get_status($process);

            if (! $status['running']) {
                break;
            }

            $output .= stream_get_contents($pipes[1]) ?: '';
            $errorOutput .= stream_get_contents($pipes[2]) ?: '';

            if ((time() - $startedAt) >= $timeout) {
                proc_terminate($process);

                break;
            }

            usleep(100_000);
        }

        $output .= stream_get_contents($pipes[1]) ?: '';
        $errorOutput .= stream_get_contents($pipes[2]) ?: '';

        fclose($pipes[1]);
        fclose($pipes[2]);

        proc_close($process);

        $contents = $output !== '' ? $output : $errorOutput;

        return $contents !== '' ? $contents : null;
    }

    private function commandTimeout(): int
    {
        $configuredTimeout = config('radar.command_timeout', 60);
        $timeout = false;

        if (is_int($configuredTimeout) || is_string($configuredTimeout)) {
            $timeout = filter_var($configuredTimeout, FILTER_VALIDATE_INT);
        }

        return is_int($timeout) && $timeout > 0 ? $timeout : 60;
    }

    /**
     * Extracts a JSON object from command output that may be
     * prefixed with warnings or other non-JSON text.
     *
     * @return array<mixed>|null
     */
    private function decodeJsonFromOutput(string $output): ?array
    {
        $decoded = json_decode($output, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $length = mb_strlen($output);

        for ($offset = 0; $offset < $length; $offset++) {
            $char = mb_substr($output, $offset, 1);

            if ($char !== '{' && $char !== '[') {
                continue;
            }

            $decoded = json_decode(mb_substr($output, $offset), true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
