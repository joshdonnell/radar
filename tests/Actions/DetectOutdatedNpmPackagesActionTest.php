<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectOutdatedNpmPackagesAction;
use JoshDonnell\Radar\Actions\ParseNpmPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseNpmPackagesAction::class)->execute($this->basepath);
});

it('detects outdated direct npm packages', function (): void {
    $findings = app(DetectOutdatedNpmPackagesAction::class)->execute($this->basepath, $this->packages);

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
    $findings = app(DetectOutdatedNpmPackagesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($findings)->toBe([]);
});
