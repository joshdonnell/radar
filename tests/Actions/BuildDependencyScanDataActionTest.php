<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\BuildDependencyScanDataAction;

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
