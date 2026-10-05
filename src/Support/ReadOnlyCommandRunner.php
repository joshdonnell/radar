<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use JoshDonnell\Radar\Exceptions\CommandFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Runs the read-only package manager commands Radar relies on. Commands can be
 * started ahead of time so independent audits run in parallel, then collected
 * later by the detector that needs them.
 */
final class ReadOnlyCommandRunner
{
    /** @var array<string, Process> */
    private array $started = [];

    /** @param list<string> $command */
    public function start(array $command, string $basePath): void
    {
        $key = $this->key($command, $basePath);

        if (isset($this->started[$key])) {
            return;
        }

        $process = new Process(
            $command,
            $basePath,
            $this->environment(),
            timeout: $this->timeout(),
        );

        try {
            $process->start();
        } catch (RuntimeException) {
            // Collected (and reported) when the output is read.
        }

        $this->started[$key] = $process;
    }

    /**
     * @param  list<string>  $command
     * @return array<string, mixed>
     *
     * @throws CommandFailedException
     */
    public function json(array $command, string $basePath): array
    {
        $output = $this->output($command, $basePath);
        $decoded = $this->decodeJson($output);

        if ($decoded === null) {
            throw CommandFailedException::invalidOutput($command, $output);
        }

        if (array_is_list($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @param  list<string>  $command
     *
     * @throws CommandFailedException
     */
    public function output(array $command, string $basePath): string
    {
        if (! is_dir($basePath)) {
            throw CommandFailedException::couldNotRun($command, sprintf('the path [%s] does not exist.', $basePath));
        }

        $this->start($command, $basePath);

        $key = $this->key($command, $basePath);
        $process = $this->started[$key];

        unset($this->started[$key]);

        if (! $process->isStarted()) {
            throw CommandFailedException::couldNotRun($command, 'the process failed to start.');
        }

        try {
            $process->wait();
        } catch (ProcessTimedOutException) {
            $process->stop();

            throw CommandFailedException::timedOut($command, $this->timeout());
        }

        // Audit and outdated commands exit non-zero when they find something,
        // so the exit code alone is not a failure signal. A shell "command not
        // found" is, because nothing useful can have been printed.
        if ($process->getExitCode() === 127) {
            throw CommandFailedException::couldNotRun($command, sprintf('%s was not found on the PATH.', $command[0]));
        }

        $output = $process->getOutput() !== ''
            ? $process->getOutput()
            : $process->getErrorOutput();

        if (mb_trim($output) === '') {
            throw CommandFailedException::couldNotRun($command, 'the command produced no output.');
        }

        return $output;
    }

    /**
     * Extracts a JSON document from output that may be prefixed with warnings
     * or other non-JSON text.
     *
     * @return array<mixed>|null
     */
    private function decodeJson(string $output): ?array
    {
        $decoded = json_decode($output, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $offset = 0;
        $length = mb_strlen($output);

        while ($offset < $length) {
            $offset += strcspn($output, '{[', $offset);

            if ($offset >= $length) {
                return null;
            }

            $decoded = json_decode(mb_substr($output, $offset), true);

            if (is_array($decoded)) {
                return $decoded;
            }

            $offset++;
        }

        return null;
    }

    /** @param list<string> $command */
    private function key(array $command, string $basePath): string
    {
        return $basePath."\0".implode("\0", $command);
    }

    /**
     * When Radar runs from the web dashboard or a queue worker, the process
     * environment usually has a minimal PATH and may lack HOME. Composer is a
     * PHP script that needs to locate a `php` binary (and its own home for
     * caching), so a bare PATH makes `composer outdated` fail. We rebuild a
     * usable PATH (and a HOME fallback) so scans behave identically everywhere.
     *
     * @return array<string, string>
     */
    private function environment(): array
    {
        $environment = ['PATH' => $this->path()];

        $home = getenv('HOME');

        if (! is_string($home) || $home === '') {
            $environment['HOME'] = sys_get_temp_dir();
        }

        return $environment;
    }

    private function path(): string
    {
        $directories = [dirname(PHP_BINARY)];

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

    private function timeout(): int
    {
        $configuredTimeout = config('radar.command_timeout', 60);
        $timeout = false;

        if (is_int($configuredTimeout) || is_string($configuredTimeout)) {
            $timeout = filter_var($configuredTimeout, FILTER_VALIDATE_INT);
        }

        return is_int($timeout) && $timeout > 0 ? $timeout : 60;
    }
}
