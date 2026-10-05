<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectComposerVulnerabilitiesAction;
use JoshDonnell\Radar\Actions\ParseComposerPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseComposerPackagesAction::class)->execute($this->basepath);
});

it('detects composer vulnerabilities', function (): void {
    $findings = app(DetectComposerVulnerabilitiesAction::class)->execute($this->basepath, $this->packages)->findings;

    expect($findings)->toHaveCount(2);

    expect($findings[0]->toArray())->toBe([
        'id' => 'PKSA-laravel-framework-fixture',
        'ecosystem' => 'composer',
        'package_name' => 'laravel/framework',
        'installed_version' => '12.57.0',
        'severity' => 'high',
        'advisory_id' => 'PKSA-laravel-framework-fixture',
        'title' => 'Fixture Laravel advisory',
        'cve' => 'CVE-2026-1001',
        'affected_versions' => '<12.57.1',
        'patched_version' => null,
        'advisory_url' => 'https://github.com/advisories/GHSA-laravel-fixture',
        'is_direct' => true,
        'recommendation' => 'Review the advisory before updating.',
        'suggested_command' => 'composer update laravel/framework --with-dependencies',
        'alternative_commands' => [],
        'required_by' => [],
    ]);

    expect($findings[1]->toArray())->toMatchArray([
        'package_name' => 'symfony/console',
        'severity' => 'medium',
        'is_direct' => false,
        'recommendation' => 'symfony/console is a transitive dependency. Try the suggested command first, and if it cannot reach a patched version, update the package that requires it rather than editing the lock file manually.',
        'suggested_command' => 'composer update symfony/console',
    ]);
});

it('returns an empty list when composer audit output is missing', function (): void {
    $result = app(DetectComposerVulnerabilitiesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($result->findings)->toBe([])
        ->and($result->warnings)->toHaveCount(1)
        ->and($result->warnings[0]->toArray())->toMatchArray([
            'ecosystem' => 'composer',
            'check' => 'vulnerabilities',
        ])
        ->and($result->warnings[0]->message)->toContain('`composer audit --format=json` could not be run');
});
