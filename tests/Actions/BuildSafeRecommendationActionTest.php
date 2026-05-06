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

    expect($action->forVulnerability($finding))->toBe('Review the advisory before updating.')
        ->and($action->commandForVulnerabilityFields(
            isDirect: $finding->isDirect,
            packageName: $finding->packageName,
            ecosystem: $finding->ecosystem,
        ))->toBe('composer update laravel/framework --with-dependencies');
});

it('recommends reviewing the parent package for transitive vulnerabilities', function (): void {
    $recommendation = app(BuildSafeRecommendationAction::class)->forVulnerability(
        recommendationVulnerabilityFinding(Ecosystem::Composer, isDirect: false),
    );

    expect($recommendation)->toBe('Review which direct dependency requires laravel/framework before updating. Prefer updating the parent package rather than editing the lock file manually.');
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

    expect($action->forVulnerability(finding: $finding))->toBe('Review the advisory before updating.')
        ->and($action->commandForVulnerabilityFields(
            isDirect: $finding->isDirect,
            packageName: $finding->packageName,
            ecosystem: $finding->ecosystem,
            nodeRunner: NodeRunner::Bun,
        ))->toBe('bun update laravel/framework');
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
