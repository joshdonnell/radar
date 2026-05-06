<?php

declare(strict_types=1);

use JoshDonnell\Radar\Enums\NodeRunner;

it('maps lock files to node runners', function (string $filename, NodeRunner $runner): void {
    expect(NodeRunner::fromLockFile($filename))->toBe($runner);
})->with([
    ['package-lock.json', NodeRunner::Npm],
    ['npm-shrinkwrap.json', NodeRunner::Npm],
    ['yarn.lock', NodeRunner::Yarn],
    ['pnpm-lock.yaml', NodeRunner::Pnpm],
    ['bun.lock', NodeRunner::Bun],
    ['bun.lockb', NodeRunner::Bun],
]);

it('detects the node runner from a project path', function (): void {
    $basepath = sys_get_temp_dir().'/radar-node-runner-'.bin2hex(random_bytes(4));

    mkdir($basepath);
    touch($basepath.'/pnpm-lock.yaml');

    try {
        expect(NodeRunner::fromProjectPath($basepath))->toBe(NodeRunner::Pnpm);
    } finally {
        unlink($basepath.'/pnpm-lock.yaml');
        rmdir($basepath);
    }
});

it('defaults to npm when no known lock file exists', function (): void {
    $basepath = sys_get_temp_dir().'/radar-node-runner-'.bin2hex(random_bytes(4));

    mkdir($basepath);

    try {
        expect(NodeRunner::fromProjectPath($basepath))->toBe(NodeRunner::Npm);
    } finally {
        rmdir($basepath);
    }
});

it('builds update commands for each runner', function (NodeRunner $runner, string $command): void {
    expect($runner->updateCommand('vite'))->toBe($command);
})->with([
    [NodeRunner::Npm, 'npm update vite'],
    [NodeRunner::Yarn, 'yarn up vite'],
    [NodeRunner::Pnpm, 'pnpm update vite'],
    [NodeRunner::Bun, 'bun update vite'],
]);
