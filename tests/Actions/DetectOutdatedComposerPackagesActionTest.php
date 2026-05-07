<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectOutdatedComposerPackagesAction;
use JoshDonnell\Radar\Actions\ParseComposerPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseComposerPackagesAction::class)->execute($this->basepath);
});

it('detects outdated direct composer packages', function (): void {
    $findings = app(DetectOutdatedComposerPackagesAction::class)->execute($this->basepath, $this->packages);

    expect($findings)->toHaveCount(2);

    expect($findings[0]->toArray())->toBe([
        'id' => 'composer-laravel/framework-outdated',
        'ecosystem' => 'composer',
        'package_name' => 'laravel/framework',
        'current_version' => '12.57.0',
        'latest_version' => '12.57.1',
        'update_type' => 'patch',
        'dependency_type' => 'production',
        'is_direct' => true,
        'suggested_command' => 'composer update laravel/framework --with-dependencies',
    ]);

    expect($findings[1]->toArray())->toBe([
        'id' => 'composer-pestphp/pest-outdated',
        'ecosystem' => 'composer',
        'package_name' => 'pestphp/pest',
        'current_version' => '4.7.0',
        'latest_version' => '5.0.0',
        'update_type' => 'major',
        'dependency_type' => 'development',
        'is_direct' => true,
        'suggested_command' => 'composer update pestphp/pest --with-dependencies',
    ]);
});

it('returns an empty list when composer outdated output is missing', function (): void {
    $findings = app(DetectOutdatedComposerPackagesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($findings)->toBe([]);
});
