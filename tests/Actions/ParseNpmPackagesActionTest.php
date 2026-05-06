<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\ParseNpmPackagesAction;
use JoshDonnell\Radar\Data\PackageData;

it('parses npm packages from package lock files', function (): void {
    $packages = app(ParseNpmPackagesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures',
    );

    expect($packages)->toHaveCount(6);

    expect($packages[0]->toArray())->toBe([
        'id' => 'npm-vite',
        'ecosystem' => 'npm',
        'name' => 'vite',
        'installed_version' => '7.0.1',
        'dependency_type' => 'production',
        'is_direct' => true,
        'source_url' => 'https://registry.npmjs.org/vite/-/vite-7.0.1.tgz',
        'required_by' => [],
    ]);

    expect($packages[1]->toArray())->toBe([
        'id' => 'npm-@vueuse/core',
        'ecosystem' => 'npm',
        'name' => '@vueuse/core',
        'installed_version' => '14.0.0',
        'dependency_type' => 'production',
        'is_direct' => true,
        'source_url' => 'https://registry.npmjs.org/@vueuse/core/-/core-14.0.0.tgz',
        'required_by' => [],
    ]);

    expect($packages[2]->toArray())->toBe([
        'id' => 'npm-typescript',
        'ecosystem' => 'npm',
        'name' => 'typescript',
        'installed_version' => '6.0.3',
        'dependency_type' => 'development',
        'is_direct' => true,
        'source_url' => 'https://registry.npmjs.org/typescript/-/typescript-6.0.3.tgz',
        'required_by' => [],
    ]);

    expect($packages[3]->toArray())->toBe([
        'id' => 'npm-vue',
        'ecosystem' => 'npm',
        'name' => 'vue',
        'installed_version' => '3.5.33',
        'dependency_type' => 'peer',
        'is_direct' => true,
        'source_url' => 'https://registry.npmjs.org/vue/-/vue-3.5.33.tgz',
        'required_by' => [],
    ]);

    expect($packages[4]->toArray())->toBe([
        'id' => 'npm-rollup-4.53.3-0ee2c474',
        'ecosystem' => 'npm',
        'name' => 'rollup',
        'installed_version' => '4.53.3',
        'dependency_type' => 'production',
        'is_direct' => false,
        'source_url' => 'https://registry.npmjs.org/rollup/-/rollup-4.53.3.tgz',
        'required_by' => ['vite'],
    ]);

    expect($packages[5]->toArray())->toBe([
        'id' => 'npm-@vueuse/shared-14.0.0-80b67cf0',
        'ecosystem' => 'npm',
        'name' => '@vueuse/shared',
        'installed_version' => '14.0.0',
        'dependency_type' => 'production',
        'is_direct' => false,
        'source_url' => 'https://registry.npmjs.org/@vueuse/shared/-/shared-14.0.0.tgz',
        'required_by' => ['@vueuse/core'],
    ]);
});

it('parses nested npm lockfile version one dependencies', function (): void {
    $basepath = sys_get_temp_dir().'/radar-npm-v1-'.bin2hex(random_bytes(4));

    mkdir($basepath);

    file_put_contents($basepath.'/package.json', json_encode([
        'dependencies' => [
            'left-pad' => '^1.3.0',
        ],
    ], JSON_PRETTY_PRINT));

    file_put_contents($basepath.'/package-lock.json', json_encode([
        'lockfileVersion' => 1,
        'dependencies' => [
            'left-pad' => [
                'version' => '1.3.0',
                'requires' => [
                    'repeat-string' => '^1.6.1',
                ],
                'dependencies' => [
                    'repeat-string' => [
                        'version' => '1.6.1',
                    ],
                ],
            ],
        ],
    ], JSON_PRETTY_PRINT));

    try {
        $packages = app(ParseNpmPackagesAction::class)->execute($basepath);

        expect($packages)->toHaveCount(2)
            ->and($packages[0]->toArray())->toMatchArray([
                'name' => 'left-pad',
                'is_direct' => true,
                'required_by' => [],
            ])
            ->and($packages[1]->toArray())->toMatchArray([
                'name' => 'repeat-string',
                'is_direct' => false,
                'required_by' => ['left-pad'],
            ]);
    } finally {
        unlink($basepath.'/package-lock.json');
        unlink($basepath.'/package.json');
        rmdir($basepath);
    }
});

it('keeps multiple locked versions of the same npm package', function (): void {
    $basepath = sys_get_temp_dir().'/radar-npm-duplicates-'.bin2hex(random_bytes(4));

    mkdir($basepath);

    file_put_contents($basepath.'/package.json', json_encode([
        'dependencies' => [
            'vite' => '^7.0.0',
            'other-tool' => '^1.0.0',
        ],
    ], JSON_PRETTY_PRINT));

    file_put_contents($basepath.'/package-lock.json', json_encode([
        'lockfileVersion' => 3,
        'packages' => [
            '' => [],
            'node_modules/vite' => [
                'version' => '7.0.1',
            ],
            'node_modules/other-tool' => [
                'version' => '1.0.0',
                'dependencies' => [
                    'vite' => '^6.0.0',
                ],
            ],
            'node_modules/other-tool/node_modules/vite' => [
                'version' => '6.0.0',
            ],
        ],
    ], JSON_PRETTY_PRINT));

    try {
        $packages = app(ParseNpmPackagesAction::class)->execute($basepath);
        $vitePackages = array_values(array_filter(
            $packages,
            fn (PackageData $package): bool => $package->name === 'vite',
        ));

        expect($vitePackages)->toHaveCount(2)
            ->and(array_map(fn (PackageData $package): string => $package->installedVersion, $vitePackages))
            ->toBe(['7.0.1', '6.0.0']);
    } finally {
        unlink($basepath.'/package-lock.json');
        unlink($basepath.'/package.json');
        rmdir($basepath);
    }
});

it('falls back to node modules when package lock is missing', function (): void {
    $basepath = sys_get_temp_dir().'/radar-npm-node-modules-'.bin2hex(random_bytes(4));

    mkdir($basepath.'/node_modules/vite', recursive: true);

    file_put_contents($basepath.'/package.json', json_encode([
        'dependencies' => [
            'vite' => '^7.0.0',
        ],
    ], JSON_PRETTY_PRINT));

    file_put_contents($basepath.'/node_modules/vite/package.json', json_encode([
        'name' => 'vite',
        'version' => '7.0.1',
        'repository' => [
            'url' => 'git+https://github.com/vitejs/vite.git',
        ],
    ], JSON_PRETTY_PRINT));

    try {
        $packages = app(ParseNpmPackagesAction::class)->execute($basepath);

        expect($packages)->toHaveCount(1)
            ->and($packages[0]->toArray())->toMatchArray([
                'id' => 'npm-vite',
                'name' => 'vite',
                'installed_version' => '7.0.1',
                'dependency_type' => 'production',
                'is_direct' => true,
                'source_url' => 'git+https://github.com/vitejs/vite.git',
            ]);
    } finally {
        unlink($basepath.'/node_modules/vite/package.json');
        rmdir($basepath.'/node_modules/vite');
        rmdir($basepath.'/node_modules');
        unlink($basepath.'/package.json');
        rmdir($basepath);
    }
});

it('returns an empty list when package files are missing', function (): void {
    $packages = app(ParseNpmPackagesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures/missing-project',
    );

    expect($packages)->toBe([]);
});
