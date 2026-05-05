<?php

declare(strict_types=1);

use JoshDonnell\Radar\Data\OutdatedPackageFinding;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\UpdateType;

it('can be created from outdated package data', function (): void {
    $dto = new OutdatedPackageFinding(
        id: 'composer-laravel/framework-outdated',
        ecosystem: Ecosystem::Composer,
        packageName: 'laravel/framework',
        currentVersion: '12.57.0',
        latestVersion: '12.58.0',
        updateType: UpdateType::Patch,
        dependencyType: DependencyType::Production,
        isDirect: true,
        suggestedCommand: 'composer update laravel/framework',
    );

    expect($dto->toArray())->toBe([
        'id' => 'composer-laravel/framework-outdated',
        'ecosystem' => 'composer',
        'package_name' => 'laravel/framework',
        'current_version' => '12.57.0',
        'latest_version' => '12.58.0',
        'update_type' => 'patch',
        'dependency_type' => 'production',
        'is_direct' => true,
        'suggested_command' => 'composer update laravel/framework',
    ]);
});

it('allows suggested command to be missing', function (): void {
    $dto = new OutdatedPackageFinding(
        id: 'npm-vite-outdated',
        ecosystem: Ecosystem::Npm,
        packageName: 'vite',
        currentVersion: '7.0.0',
        latestVersion: '8.0.0',
        updateType: UpdateType::Major,
        dependencyType: DependencyType::Development,
        isDirect: true,
    );

    expect($dto->toArray())
        ->suggested_command->toBeNull();
});
