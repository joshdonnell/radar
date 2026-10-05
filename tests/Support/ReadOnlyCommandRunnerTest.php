<?php

declare(strict_types=1);

use JoshDonnell\Radar\Exceptions\CommandFailedException;
use JoshDonnell\Radar\Support\ReadOnlyCommandRunner;

it('decodes json printed after warnings', function (): void {
    $json = app(ReadOnlyCommandRunner::class)->json(
        [PHP_BINARY, '-r', 'echo "Warning: something [odd]\n{\"installed\": [{\"name\": \"vite\"}]}";'],
        sys_get_temp_dir(),
    );

    expect($json)->toBe(['installed' => [['name' => 'vite']]]);
});

it('treats an empty json object as an empty report', function (): void {
    expect(app(ReadOnlyCommandRunner::class)->json([PHP_BINARY, '-r', 'echo "{}";'], sys_get_temp_dir()))->toBe([]);
});

it('fails when the output is not json', function (): void {
    app(ReadOnlyCommandRunner::class)->json([PHP_BINARY, '-r', 'echo "Could not connect to packagist.org";'], sys_get_temp_dir());
})->throws(CommandFailedException::class, 'did not return valid JSON: Could not connect to packagist.org');

it('fails when the command is not installed', function (): void {
    app(ReadOnlyCommandRunner::class)->json(['radar-missing-binary', 'audit'], sys_get_temp_dir());
})->throws(CommandFailedException::class, '`radar-missing-binary audit` could not be run: radar-missing-binary was not found on the PATH.');

it('fails when the command times out', function (): void {
    config(['radar.command_timeout' => 1]);

    app(ReadOnlyCommandRunner::class)->json([PHP_BINARY, '-r', 'sleep(5);'], sys_get_temp_dir());
})->throws(CommandFailedException::class, 'timed out after 1s.');

it('fails when the command produces no output', function (): void {
    app(ReadOnlyCommandRunner::class)->json([PHP_BINARY, '-r', 'exit(0);'], sys_get_temp_dir());
})->throws(CommandFailedException::class, 'the command produced no output.');

it('runs started commands in parallel', function (): void {
    $runner = app(ReadOnlyCommandRunner::class);
    $first = [PHP_BINARY, '-r', 'usleep(800000); echo "{\"first\": true}";'];
    $second = [PHP_BINARY, '-r', 'usleep(800000); echo "{\"second\": true}";'];

    $startedAt = microtime(true);

    $runner->start($first, sys_get_temp_dir());
    $runner->start($second, sys_get_temp_dir());

    expect($runner->json($first, sys_get_temp_dir()))->toBe(['first' => true])
        ->and($runner->json($second, sys_get_temp_dir()))->toBe(['second' => true])
        ->and(microtime(true) - $startedAt)->toBeLessThan(1.5);
});
