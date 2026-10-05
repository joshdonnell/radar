<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\BuildDependencyScanDataAction;
use JoshDonnell\Radar\Data\ScanWarningData;

it('builds dependency scan data from composer and npm packages', function (): void {
    $scan = app(BuildDependencyScanDataAction::class)->execute(
        basepath: __DIR__.'/../Fixtures',
    );

    expect($scan->packages)->toHaveCount(12)
        ->and($scan->vulnerabilities)->toHaveCount(4)
        ->and($scan->outdated)->toHaveCount(4)
        ->and($scan->abandoned)->toHaveCount(2);
});

it('returns empty dependency scan data when no package inventory exists', function (): void {
    $scan = app(BuildDependencyScanDataAction::class)->execute(
        basepath: __DIR__.'/../Fixtures/missing-project',
    );

    expect($scan->packages)->toBe([])
        ->and($scan->vulnerabilities)->toBe([])
        ->and($scan->outdated)->toBe([])
        ->and($scan->abandoned)->toBe([]);
});

it('warns that the node inventory is limited without a package-lock file', function (): void {
    $scan = app(BuildDependencyScanDataAction::class)->execute(__DIR__.'/../Fixtures/bun-project');

    expect(array_map(fn (ScanWarningData $warning): string => $warning->check->value, $scan->warnings))
        ->toBe(['inventory', 'vulnerabilities', 'outdated'])
        ->and($scan->warnings[0]->message)->toBe('Radar reads the full Node package tree from package-lock.json only. For this Bun project, only direct dependencies are listed.');
});
