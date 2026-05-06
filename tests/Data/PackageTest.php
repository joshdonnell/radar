<?php

declare(strict_types=1);

use JoshDonnell\Radar\Data\Package;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

it('can be created from package data', function (): void {
    $dto = new Package(
        id: '9efafd7f-f9cf-42c0-aa90-c8ed8e387f13',
        ecosystem: Ecosystem::Composer,
        name: 'laravel/framework',
        installedVersion: '12.57.0',
        dependencyType: DependencyType::Production,
        isDirect: true,
        sourceUrl: 'https://github.com/laravel/framework',
        requiredBy: ['joshdonnell/radar'],
    );

    expect($dto->toArray())->toBe([
        'id' => '9efafd7f-f9cf-42c0-aa90-c8ed8e387f13',
        'ecosystem' => 'composer',
        'name' => 'laravel/framework',
        'installed_version' => '12.57.0',
        'dependency_type' => 'production',
        'is_direct' => true,
        'source_url' => 'https://github.com/laravel/framework',
        'required_by' => ['joshdonnell/radar'],
    ]);
});

it('default is_direct to false', function (): void {
    $dto = new Package(
        id: '9efafd7f-f9cf-42c0-aa90-c8ed8e387f13',
        ecosystem: Ecosystem::Composer,
        name: 'laravel/framework',
        installedVersion: '12.57.0',
        dependencyType: DependencyType::Production,
    );

    expect($dto->toArray())->toBe([
        'id' => '9efafd7f-f9cf-42c0-aa90-c8ed8e387f13',
        'ecosystem' => 'composer',
        'name' => 'laravel/framework',
        'installed_version' => '12.57.0',
        'dependency_type' => 'production',
        'is_direct' => false,
        'source_url' => null,
        'required_by' => [],
    ]);
});

it('allows source_url to be null', function (): void {
    $dto = new Package(
        id: '9efafd7f-f9cf-42c0-aa90-c8ed8e387f13',
        ecosystem: Ecosystem::Composer,
        name: 'laravel/framework',
        installedVersion: '12.57.0',
        dependencyType: DependencyType::Production,
    );

    expect($dto->toArray())->toBe([
        'id' => '9efafd7f-f9cf-42c0-aa90-c8ed8e387f13',
        'ecosystem' => 'composer',
        'name' => 'laravel/framework',
        'installed_version' => '12.57.0',
        'dependency_type' => 'production',
        'is_direct' => false,
        'source_url' => null,
        'required_by' => [],
    ]);
});
