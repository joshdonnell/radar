<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\ParseComposerPackagesAction;

beforeEach(function (): void {
    $this->packages = app(ParseComposerPackagesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures',
    );
});

it('parses composer packages from composer files', function (): void {
    expect($this->packages)->toHaveCount(6);

    expect($this->packages[0]->toArray())->toBe([
        'id' => 'composer-laravel/framework',
        'ecosystem' => 'composer',
        'name' => 'laravel/framework',
        'installed_version' => '12.57.0',
        'dependency_type' => 'production',
        'is_direct' => true,
        'source_url' => 'https://github.com/laravel/framework',
        'required_by' => [],
    ]);

    expect($this->packages[1]->toArray())->toBe([
        'id' => 'composer-spatie/laravel-package-tools',
        'ecosystem' => 'composer',
        'name' => 'spatie/laravel-package-tools',
        'installed_version' => '1.93.0',
        'dependency_type' => 'production',
        'is_direct' => true,
        'source_url' => 'https://github.com/spatie/laravel-package-tools',
        'required_by' => [],
    ]);

    expect($this->packages[2]->toArray())->toBe([
        'id' => 'composer-symfony/console',
        'ecosystem' => 'composer',
        'name' => 'symfony/console',
        'installed_version' => '7.3.0',
        'dependency_type' => 'production',
        'is_direct' => false,
        'source_url' => 'https://github.com/symfony/console',
        'required_by' => ['laravel/framework', 'orchestra/testbench'],
    ]);

    expect($this->packages[3]->toArray())->toBe([
        'id' => 'composer-pestphp/pest',
        'ecosystem' => 'composer',
        'name' => 'pestphp/pest',
        'installed_version' => '4.7.0',
        'dependency_type' => 'development',
        'is_direct' => true,
        'source_url' => 'https://github.com/pestphp/pest',
        'required_by' => [],
    ]);

    expect($this->packages[4]->toArray())->toBe([
        'id' => 'composer-orchestra/testbench',
        'ecosystem' => 'composer',
        'name' => 'orchestra/testbench',
        'installed_version' => '10.6.0',
        'dependency_type' => 'development',
        'is_direct' => true,
        'source_url' => 'https://github.com/orchestral/testbench',
        'required_by' => [],
    ]);

    expect($this->packages[5]->toArray())->toBe([
        'id' => 'composer-phpunit/phpunit',
        'ecosystem' => 'composer',
        'name' => 'phpunit/phpunit',
        'installed_version' => '11.5.0',
        'dependency_type' => 'development',
        'is_direct' => false,
        'source_url' => 'https://github.com/sebastianbergmann/phpunit',
        'required_by' => ['orchestra/testbench'],
    ]);
});

it('marks transitive composer packages as indirect', function (): void {
    $packagesByName = collect($this->packages)->keyBy->name;

    expect($packagesByName->get('symfony/console')?->isDirect)->toBeFalse()
        ->and($packagesByName->get('phpunit/phpunit')?->isDirect)->toBeFalse()
        ->and($packagesByName->get('laravel/framework')?->isDirect)->toBeTrue()
        ->and($packagesByName->get('pestphp/pest')?->isDirect)->toBeTrue();
});

it('tracks which packages require transitive packages', function (): void {
    $packagesByName = collect($this->packages)->keyBy->name;

    expect($packagesByName->get('symfony/console')?->requiredBy)->toBe(['laravel/framework', 'orchestra/testbench'])
        ->and($packagesByName->get('phpunit/phpunit')?->requiredBy)->toBe(['orchestra/testbench'])
        ->and($packagesByName->get('laravel/framework')?->requiredBy)->toBe([]);
});

it('merges duplicate composer packages from the lock file', function (): void {
    $packageNames = collect($this->packages)->map->name;
    $symfonyConsole = collect($this->packages)->firstWhere('name', 'symfony/console');

    expect($packageNames->all())->toHaveCount($packageNames->unique()->count())
        ->and($symfonyConsole?->toArray())->toMatchArray([
            'name' => 'symfony/console',
            'dependency_type' => 'production',
            'is_direct' => false,
            'required_by' => ['laravel/framework', 'orchestra/testbench'],
        ]);
});

it('falls back to composer installed json when composer lock is missing', function (): void {
    $basepath = sys_get_temp_dir().'/radar-composer-installed-'.bin2hex(random_bytes(4));

    mkdir($basepath.'/vendor/composer', recursive: true);

    file_put_contents($basepath.'/composer.json', json_encode([
        'require' => [
            'laravel/framework' => '^12.0',
        ],
    ], JSON_PRETTY_PRINT));

    file_put_contents($basepath.'/vendor/composer/installed.json', json_encode([
        'packages' => [
            [
                'name' => 'laravel/framework',
                'version' => 'v12.57.0',
                'require' => [
                    'symfony/console' => '^7.0',
                ],
            ],
            [
                'name' => 'symfony/console',
                'version' => 'v7.3.0',
            ],
        ],
    ], JSON_PRETTY_PRINT));

    try {
        $packages = app(ParseComposerPackagesAction::class)->execute($basepath);

        expect($packages)->toHaveCount(2)
            ->and($packages[0]->toArray())->toMatchArray([
                'name' => 'laravel/framework',
                'installed_version' => '12.57.0',
                'is_direct' => true,
            ])
            ->and($packages[1]->toArray())->toMatchArray([
                'name' => 'symfony/console',
                'installed_version' => '7.3.0',
                'is_direct' => false,
                'required_by' => ['laravel/framework'],
            ]);
    } finally {
        unlink($basepath.'/vendor/composer/installed.json');
        rmdir($basepath.'/vendor/composer');
        rmdir($basepath.'/vendor');
        unlink($basepath.'/composer.json');
        rmdir($basepath);
    }
});

it('returns an empty list when composer files are missing', function (): void {
    $packages = app(ParseComposerPackagesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures/missing-project',
    );

    expect($packages)->toBe([]);
});
