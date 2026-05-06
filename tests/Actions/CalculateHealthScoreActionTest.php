<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\CalculateHealthScoreAction;
use JoshDonnell\Radar\Data\AbandonedPackageFindingData;
use JoshDonnell\Radar\Data\OutdatedPackageFindingData;
use JoshDonnell\Radar\Data\VulnerabilityFindingData;
use JoshDonnell\Radar\Enums\DependencyType;
use JoshDonnell\Radar\Enums\Ecosystem;
use JoshDonnell\Radar\Enums\UpdateType;
use JoshDonnell\Radar\Enums\VulnerabilitySeverity;

it('returns a perfect score when no findings exist', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute();

    expect($score)->toBe(100);
});

it('penalizes vulnerability severity predictably', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute(vulnerabilities: [
        vulnerability(VulnerabilitySeverity::Critical),
        vulnerability(VulnerabilitySeverity::High),
        vulnerability(VulnerabilitySeverity::Medium),
        vulnerability(VulnerabilitySeverity::Low),
        vulnerability(VulnerabilitySeverity::Unknown),
    ]);

    expect($score)->toBe(58);
});

it('keeps a non zero score for a handful of transitive vulnerabilities', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute(vulnerabilities: [
        vulnerability(VulnerabilitySeverity::High, isDirect: false),
        vulnerability(VulnerabilitySeverity::High, isDirect: false),
        vulnerability(VulnerabilitySeverity::High, isDirect: false),
        vulnerability(VulnerabilitySeverity::Medium, isDirect: false),
        vulnerability(VulnerabilitySeverity::Medium, isDirect: false),
        vulnerability(VulnerabilitySeverity::Low, isDirect: false),
        vulnerability(VulnerabilitySeverity::Low, isDirect: false),
    ]);

    expect($score)->toBe(72);
});

it('penalizes outdated direct dependencies predictably', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute(outdatedPackages: [
        outdatedPackage(UpdateType::Major),
        outdatedPackage(UpdateType::Minor),
        outdatedPackage(UpdateType::Patch),
        outdatedPackage(UpdateType::Unknown),
    ]);

    expect($score)->toBe(85);
});

it('penalizes transitive outdated dependencies less than direct ones', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute(outdatedPackages: [
        outdatedPackage(UpdateType::Major, isDirect: false),
        outdatedPackage(UpdateType::Minor, isDirect: false),
        outdatedPackage(UpdateType::Patch, isDirect: false),
        outdatedPackage(UpdateType::Unknown, isDirect: false),
    ]);

    expect($score)->toBe(92);
});

it('penalizes abandoned packages predictably', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute(abandonedPackages: [
        abandonedPackage(isDirect: true),
        abandonedPackage(isDirect: false),
    ]);

    expect($score)->toBe(91);
});

it('never returns less than zero', function (): void {
    $score = app(CalculateHealthScoreAction::class)->execute(vulnerabilities: [
        vulnerability(VulnerabilitySeverity::Critical),
        vulnerability(VulnerabilitySeverity::Critical),
        vulnerability(VulnerabilitySeverity::Critical),
        vulnerability(VulnerabilitySeverity::Critical),
        vulnerability(VulnerabilitySeverity::Critical),
        vulnerability(VulnerabilitySeverity::Critical),
    ]);

    expect($score)->toBe(0);
});

function vulnerability(VulnerabilitySeverity $severity, bool $isDirect = true): VulnerabilityFindingData
{
    return new VulnerabilityFindingData(
        id: 'advisory-'.$severity->value,
        ecosystem: Ecosystem::Composer,
        packageName: 'laravel/framework',
        installedVersion: '12.57.0',
        severity: $severity,
        advisoryId: 'advisory-'.$severity->value,
        isDirect: $isDirect,
    );
}

function outdatedPackage(UpdateType $updateType, bool $isDirect = true): OutdatedPackageFindingData
{
    return new OutdatedPackageFindingData(
        id: 'outdated-'.$updateType->value,
        ecosystem: Ecosystem::Composer,
        packageName: 'laravel/framework',
        currentVersion: '12.57.0',
        latestVersion: '12.58.0',
        updateType: $updateType,
        dependencyType: DependencyType::Production,
        isDirect: $isDirect,
    );
}

function abandonedPackage(bool $isDirect = true): AbandonedPackageFindingData
{
    return new AbandonedPackageFindingData(
        id: 'abandoned-laravel/framework',
        ecosystem: Ecosystem::Composer,
        packageName: 'laravel/framework',
        installedVersion: '12.57.0',
        dependencyType: DependencyType::Production,
        isDirect: $isDirect,
    );
}
