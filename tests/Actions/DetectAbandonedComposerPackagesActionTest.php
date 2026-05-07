<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectAbandonedComposerPackagesAction;
use JoshDonnell\Radar\Actions\ParseComposerPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseComposerPackagesAction::class)->execute($this->basepath);
});

it('detects abandoned composer packages', function (): void {
    $findings = app(DetectAbandonedComposerPackagesAction::class)->execute($this->basepath, $this->packages);

    expect($findings)->toHaveCount(2);

    expect($findings[0]->toArray())->toBe([
        'id' => 'composer-spatie/laravel-package-tools',
        'ecosystem' => 'composer',
        'package_name' => 'spatie/laravel-package-tools',
        'installed_version' => '1.93.0',
        'dependency_type' => 'production',
        'is_direct' => true,
        'replacement_package' => 'spatie/package-tools',
        'recommendation' => 'Replace spatie/laravel-package-tools with spatie/package-tools after reviewing compatibility.',
    ]);

    expect($findings[1]->toArray())->toMatchArray([
        'package_name' => 'phpunit/phpunit',
        'replacement_package' => null,
        'is_direct' => false,
        'recommendation' => 'Review phpunit/phpunit and replace it with a maintained alternative when possible.',
    ]);
});

it('returns an empty list when composer files are missing', function (): void {
    $findings = app(DetectAbandonedComposerPackagesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($findings)->toBe([]);
});
