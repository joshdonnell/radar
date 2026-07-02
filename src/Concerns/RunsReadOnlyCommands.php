<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

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

        $process = new Process(
            $command,
            $basePath,
            $this->commandEnvironment(),
            timeout: $this->commandTimeout(),
        );

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            $process->stop();
        }

        $contents = $process->getOutput() !== ''
            ? $process->getOutput()
            : $process->getErrorOutput();

        return $contents !== '' ? $contents : null;
    }

    /**
     * Environment overrides for the subprocess.
     *
     * When Radar runs from the web dashboard, the request runs under a web
     * server (PHP-FPM, Herd, Valet) whose environment usually has a minimal
     * PATH and may lack HOME. Composer is a PHP script that needs to locate a
     * `php` binary (and its own home for caching) to resolve the latest package
     * versions, so a bare PATH makes `composer outdated` fail silently and no
     * updates are reported. The same command works from the CLI because the
     * shell environment already exposes these. We rebuild a usable PATH (and a
     * HOME fallback) so scans behave identically from the UI and the console.
     *
     * @return array<string, string>
     */
    private function commandEnvironment(): array
    {
        $environment = ['PATH' => $this->commandPath()];

        $home = getenv('HOME');

        if (! is_string($home) || $home === '') {
            $environment['HOME'] = sys_get_temp_dir();
        }

        return $environment;
    }

    private function commandPath(): string
    {
        $directories = [];

        if (PHP_BINARY !== '') {
            $directories[] = dirname(PHP_BINARY);
        }

        $inheritedPath = getenv('PATH');

        if (is_string($inheritedPath) && $inheritedPath !== '') {
            $directories = [...$directories, ...explode(PATH_SEPARATOR, $inheritedPath)];
        }

        $directories = [
            ...$directories,
            '/usr/local/bin',
            '/opt/homebrew/bin',
            '/usr/bin',
            '/bin',
        ];

        $directories = array_values(array_unique(
            array_filter($directories, static fn (string $directory): bool => $directory !== ''),
        ));

        return implode(PATH_SEPARATOR, $directories);
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
