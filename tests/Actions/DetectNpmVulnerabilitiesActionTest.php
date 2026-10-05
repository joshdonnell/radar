<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectNpmVulnerabilitiesAction;
use JoshDonnell\Radar\Actions\ParseNpmPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseNpmPackagesAction::class)->execute($this->basepath);
});

it('detects npm vulnerabilities', function (): void {
    $findings = app(DetectNpmVulnerabilitiesAction::class)->execute($this->basepath, $this->packages)->findings;

    expect($findings)->toHaveCount(2);

    expect($findings[0]->toArray())->toBe([
        'id' => 'npm-vite-1001',
        'ecosystem' => 'npm',
        'package_name' => 'vite',
        'installed_version' => '7.0.1',
        'severity' => 'high',
        'advisory_id' => 'npm-vite-1001',
        'title' => 'Fixture Vite advisory',
        'cve' => null,
        'affected_versions' => '<7.0.2',
        'patched_version' => null,
        'advisory_url' => 'https://github.com/advisories/GHSA-vite-fixture',
        'is_direct' => true,
        'recommendation' => 'Review the advisory before updating.',
        'suggested_command' => 'npm update vite',
        'alternative_commands' => [],
        'required_by' => [],
    ]);

    expect($findings[1]->toArray())->toMatchArray([
        'id' => 'npm-rollup-1002',
        'package_name' => 'rollup',
        'severity' => 'medium',
        'is_direct' => false,
        'recommendation' => 'rollup is a transitive dependency. Try the suggested command first, and if it cannot reach a patched version, update the package that requires it rather than editing the lock file manually.',
        'suggested_command' => 'npm audit fix',
    ]);
});

it('matches duplicate npm package vulnerabilities by audit nodes', function (): void {
    $packages = app(ParseNpmPackagesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures/npm-duplicate-package-versions',
    );

    $nestedFindings = app(DetectNpmVulnerabilitiesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures/npm-audit-nested-duplicate-package-version',
        packages: $packages,
    )->findings;
    $directFindings = app(DetectNpmVulnerabilitiesAction::class)->execute(
        basepath: __DIR__.'/../Fixtures/npm-audit-direct-duplicate-package-version',
        packages: $packages,
    )->findings;

    expect($nestedFindings)->toHaveCount(1)
        ->and($nestedFindings[0]->toArray())->toMatchArray([
            'id' => 'npm-vite-2001',
            'package_name' => 'vite',
            'installed_version' => '6.0.0',
            'is_direct' => false,
            'recommendation' => 'vite is a transitive dependency. Try the suggested command first, and if it cannot reach a patched version, update the package that requires it rather than editing the lock file manually.',
            'suggested_command' => 'npm audit fix',
            'required_by' => ['other-tool'],
        ])
        ->and($directFindings)->toHaveCount(1)
        ->and($directFindings[0]->toArray())->toMatchArray([
            'id' => 'npm-vite-2002',
            'package_name' => 'vite',
            'installed_version' => '7.0.1',
            'is_direct' => true,
            'recommendation' => 'Review the advisory before updating.',
            'suggested_command' => 'npm update vite',
        ]);
});

it('returns an empty list when npm audit output is missing', function (): void {
    $result = app(DetectNpmVulnerabilitiesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($result->findings)->toBe([])
        ->and($result->warnings)->toHaveCount(1)
        ->and($result->warnings[0]->toArray())->toMatchArray([
            'ecosystem' => 'npm',
            'check' => 'vulnerabilities',
        ])
        ->and($result->warnings[0]->message)->toContain('`npm audit --json` could not be run');
});

it('keeps transitive advisories for packages missing from a pnpm inventory', function (): void {
    $basepath = __DIR__.'/../Fixtures/pnpm-transitive-audit';
    $packages = app(ParseNpmPackagesAction::class)->execute($basepath);

    $findings = app(DetectNpmVulnerabilitiesAction::class)->execute($basepath, $packages)->findings;

    expect($findings)->toHaveCount(1)
        ->and($findings[0]->toArray())->toMatchArray([
            'id' => 'npm-esbuild-1100',
            'package_name' => 'esbuild',
            'installed_version' => '0.24.2',
            'severity' => 'medium',
            'title' => 'Fixture esbuild advisory',
            'is_direct' => false,
            'suggested_command' => null,
        ]);
});

it('warns instead of reporting a clean audit for bun projects', function (): void {
    $basepath = __DIR__.'/../Fixtures/bun-project';
    $packages = app(ParseNpmPackagesAction::class)->execute($basepath);

    $result = app(DetectNpmVulnerabilitiesAction::class)->execute($basepath, $packages);

    expect($result->findings)->toBe([])
        ->and($result->warnings)->toHaveCount(1)
        ->and($result->warnings[0]->message)->toBe('Radar cannot audit Bun projects yet, so Node packages were not checked for vulnerabilities.');
});
