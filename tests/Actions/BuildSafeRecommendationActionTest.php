<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\BuildSafeRecommendationAction;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\NodeRunner;
use JoshDonnell\Radar\Enums\UpdateType;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;

it('recommends advisory review text and a structured composer command for direct vulnerabilities', function (): void {
    $action = app(BuildSafeRecommendationAction::class);
    $finding = recommendationVulnerabilityFinding(Ecosystem::Composer, isDirect: true);

    expect($action->forVulnerability($finding->isDirect, $finding->packageName))->toBe('Review the advisory before updating.')
        ->and($action->commandForVulnerability(
            ecosystem: $finding->ecosystem,
            packageName: $finding->packageName,
            isDirect: $finding->isDirect,
        ))->toBe('composer update laravel/framework --with-dependencies')
        ->and($action->alternativeCommandsForVulnerability(
            ecosystem: $finding->ecosystem,
            packageName: $finding->packageName,
            isDirect: $finding->isDirect,
            requiredBy: ['acme/parent'],
        ))->toBe([]);
});

it('recommends reviewing the parent package for transitive vulnerabilities', function (): void {
    $recommendation = app(BuildSafeRecommendationAction::class)->forVulnerability(
        isDirect: false,
        packageName: 'laravel/framework',
    );

    expect($recommendation)->toBe('laravel/framework is a transitive dependency. Try the suggested command first, and if it cannot reach a patched version, update the package that requires it rather than editing the lock file manually.');
});

it('recommends changelog review text and a structured node runner command for direct outdated packages', function (): void {
    $action = app(BuildSafeRecommendationAction::class);
    $finding = recommendationOutdatedPackage(Ecosystem::Npm, isDirect: true);

    expect($action->forOutdatedPackage(finding: $finding))->toBe('Review the changelog before updating.')
        ->and($action->commandForOutdatedPackage(finding: $finding, nodeRunner: NodeRunner::Pnpm))->toBe('pnpm update laravel/framework');
});

it('uses the detected node runner for direct npm vulnerability commands', function (): void {
    $action = app(BuildSafeRecommendationAction::class);
    $finding = recommendationVulnerabilityFinding(Ecosystem::Npm, isDirect: true);

    expect($action->commandForVulnerability(
        ecosystem: $finding->ecosystem,
        packageName: $finding->packageName,
        isDirect: $finding->isDirect,
        nodeRunner: NodeRunner::Bun,
    ))->toBe('bun update laravel/framework');
});

it('updates a transitive composer vulnerability directly and offers parent updates as alternatives', function (): void {
    $action = app(BuildSafeRecommendationAction::class);

    expect($action->commandForVulnerability(
        ecosystem: Ecosystem::Composer,
        packageName: 'symfony/http-kernel',
        isDirect: false,
        requiredBy: ['laravel/framework', 'acme/package'],
    ))->toBe('composer update symfony/http-kernel')
        ->and($action->alternativeCommandsForVulnerability(
            ecosystem: Ecosystem::Composer,
            packageName: 'symfony/http-kernel',
            isDirect: false,
            requiredBy: ['laravel/framework', 'acme/package'],
        ))->toBe([
            'composer update laravel/framework --with-dependencies',
            'composer update acme/package --with-dependencies',
        ]);
});

it('uses the node runner fix command for transitive npm vulnerabilities', function (NodeRunner $nodeRunner, ?string $command, array $alternatives): void {
    $action = app(BuildSafeRecommendationAction::class);

    expect($action->commandForVulnerability(
        ecosystem: Ecosystem::Npm,
        packageName: 'rollup',
        isDirect: false,
        requiredBy: ['vite', 'vitest'],
        nodeRunner: $nodeRunner,
    ))->toBe($command)
        ->and($action->alternativeCommandsForVulnerability(
            ecosystem: Ecosystem::Npm,
            packageName: 'rollup',
            isDirect: false,
            requiredBy: ['vite', 'vitest'],
            nodeRunner: $nodeRunner,
        ))->toBe($alternatives);
})->with([
    'npm' => [NodeRunner::Npm, 'npm audit fix', ['npm update vite', 'npm update vitest']],
    'yarn' => [NodeRunner::Yarn, 'yarn up -R rollup', ['yarn up vite', 'yarn up vitest']],
    'pnpm' => [NodeRunner::Pnpm, 'pnpm update vite', ['pnpm update vitest']],
]);

it('has no command for a transitive bun vulnerability without known parents', function (): void {
    expect(app(BuildSafeRecommendationAction::class)->commandForVulnerability(
        ecosystem: Ecosystem::Npm,
        packageName: 'rollup',
        isDirect: false,
        nodeRunner: NodeRunner::Bun,
    ))->toBeNull();
});

it('recommends reviewing the parent package for transitive outdated packages', function (): void {
    $recommendation = app(BuildSafeRecommendationAction::class)->forOutdatedPackage(
        recommendationOutdatedPackage(Ecosystem::Composer, isDirect: false),
    );

    expect($recommendation)->toBe('Review which direct dependency requires laravel/framework before updating this transitive package.');
});

function recommendationVulnerabilityFinding(Ecosystem $ecosystem, bool $isDirect): VulnerabilityFindingData
{
    return new VulnerabilityFindingData(
        id: 'GHSA-xxxx-yyyy-zzzz',
        ecosystem: $ecosystem,
        packageName: 'laravel/framework',
        installedVersion: '12.57.0',
        severity: VulnerabilitySeverity::High,
        advisoryId: 'GHSA-xxxx-yyyy-zzzz',
        isDirect: $isDirect,
    );
}

function recommendationOutdatedPackage(Ecosystem $ecosystem, bool $isDirect): OutdatedPackageFindingData
{
    return new OutdatedPackageFindingData(
        id: 'outdated-laravel/framework',
        ecosystem: $ecosystem,
        packageName: 'laravel/framework',
        currentVersion: '12.57.0',
        latestVersion: '12.58.0',
        updateType: UpdateType::Patch,
        dependencyType: DependencyType::Production,
        isDirect: $isDirect,
    );
}
