<?php

declare(strict_types=1);

use JoshDonnell\Radar\Data\AbandonedPackageFinding;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;

it('can be created from abandoned package data', function (): void {
    $finding = new AbandonedPackageFinding(
        id: 'composer-acme/old-package',
        ecosystem: Ecosystem::Composer,
        packageName: 'acme/old-package',
        installedVersion: '1.0.0',
        dependencyType: DependencyType::Production,
        isDirect: true,
        replacementPackage: 'acme/new-package',
        recommendation: 'Replace acme/old-package with acme/new-package.',
    );

    expect($finding->toArray())->toBe([
        'id' => 'composer-acme/old-package',
        'ecosystem' => 'composer',
        'package_name' => 'acme/old-package',
        'installed_version' => '1.0.0',
        'dependency_type' => 'production',
        'is_direct' => true,
        'replacement_package' => 'acme/new-package',
        'recommendation' => 'Replace acme/old-package with acme/new-package.',
    ]);
});

it('allows replacement details to be missing', function (): void {
    $finding = new AbandonedPackageFinding(
        id: 'composer-acme/old-package',
        ecosystem: Ecosystem::Composer,
        packageName: 'acme/old-package',
        installedVersion: '1.0.0',
        dependencyType: DependencyType::Development,
        isDirect: false,
    );

    expect($finding->toArray())
        ->replacement_package->toBeNull()
        ->recommendation->toBeNull();
});
