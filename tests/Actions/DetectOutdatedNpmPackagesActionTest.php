<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectOutdatedNpmPackagesAction;
use JoshDonnell\Radar\Actions\ParseNpmPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseNpmPackagesAction::class)->execute($this->basepath);
});

it('detects outdated direct npm packages', function (): void {
    $findings = app(DetectOutdatedNpmPackagesAction::class)->execute($this->basepath, $this->packages)->findings;

    expect($findings)->toHaveCount(2);

    expect($findings[0]->toArray())->toBe([
        'id' => 'npm-vite-outdated',
        'ecosystem' => 'npm',
        'package_name' => 'vite',
        'current_version' => '7.0.1',
        'latest_version' => '8.0.0',
        'update_type' => 'major',
        'dependency_type' => 'production',
        'is_direct' => true,
        'suggested_command' => 'npm update vite',
    ]);

    expect($findings[1]->toArray())->toBe([
        'id' => 'npm-typescript-outdated',
        'ecosystem' => 'npm',
        'package_name' => 'typescript',
        'current_version' => '6.0.3',
        'latest_version' => '6.0.4',
        'update_type' => 'patch',
        'dependency_type' => 'development',
        'is_direct' => true,
        'suggested_command' => 'npm update typescript',
    ]);
});

it('returns an empty list when npm outdated output is missing', function (): void {
    $result = app(DetectOutdatedNpmPackagesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($result->findings)->toBe([])
        ->and($result->warnings)->toHaveCount(1)
        ->and($result->warnings[0]->toArray())->toMatchArray([
            'ecosystem' => 'npm',
            'check' => 'outdated',
        ])
        ->and($result->warnings[0]->message)->toContain('`npm outdated --json` could not be run');
});

it('warns instead of reporting no updates for bun projects', function (): void {
    $basepath = __DIR__.'/../Fixtures/bun-project';
    $packages = app(ParseNpmPackagesAction::class)->execute($basepath);

    $result = app(DetectOutdatedNpmPackagesAction::class)->execute($basepath, $packages);

    expect($result->findings)->toBe([])
        ->and($result->warnings[0]->toArray())->toBe([
            'ecosystem' => 'npm',
            'check' => 'outdated',
            'message' => 'Radar cannot check Bun projects for outdated packages yet.',
        ]);
});
