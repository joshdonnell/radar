<?php

declare(strict_types=1);

use JoshDonnell\Radar\Actions\DetectNpmVulnerabilitiesAction;
use JoshDonnell\Radar\Actions\ParseNpmPackagesAction;

beforeEach(function (): void {
    $this->basepath = __DIR__.'/../Fixtures';
    $this->packages = app(ParseNpmPackagesAction::class)->execute($this->basepath);
});

it('detects npm vulnerabilities', function (): void {
    $findings = app(DetectNpmVulnerabilitiesAction::class)->execute($this->basepath, $this->packages);

    expect($findings)->toHaveCount(2);

    expect($findings[0]->toArray())->toBe([
        'id' => 'npm-vite-1001',
        'ecosystem' => 'npm',
        'package_name' => 'vite',
        'installed_version' => '7.0.1',
        'severity' => 'high',
        'advisory_id' => 'npm-vite-1001',
        'cve' => null,
        'affected_versions' => '<7.0.2',
        'patched_version' => null,
        'advisory_url' => 'https://github.com/advisories/GHSA-vite-fixture',
        'is_direct' => true,
        'recommendation' => 'Review the advisory before updating.',
        'suggested_command' => 'npm update vite',
        'required_by' => [],
    ]);

    expect($findings[1]->toArray())->toMatchArray([
        'id' => 'npm-rollup-1002',
        'package_name' => 'rollup',
        'severity' => 'medium',
        'is_direct' => false,
        'recommendation' => 'Review which direct dependency requires rollup before updating. Prefer updating the parent package rather than editing the lock file manually.',
        'suggested_command' => null,
    ]);
});

it('matches duplicate npm package vulnerabilities by audit nodes', function (): void {
    $basepath = sys_get_temp_dir().'/radar-npm-vulnerability-duplicates-'.bin2hex(random_bytes(4));

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

        file_put_contents($basepath.'/npm-audit.json', json_encode([
            'auditReportVersion' => 2,
            'vulnerabilities' => [
                'vite' => [
                    'name' => 'vite',
                    'severity' => 'high',
                    'isDirect' => false,
                    'via' => [
                        [
                            'source' => 2001,
                            'name' => 'vite',
                            'dependency' => 'vite',
                            'severity' => 'high',
                            'range' => '<6.0.1',
                        ],
                    ],
                    'range' => '<6.0.1',
                    'nodes' => ['node_modules/other-tool/node_modules/vite'],
                ],
            ],
        ], JSON_PRETTY_PRINT));

        $nestedFindings = app(DetectNpmVulnerabilitiesAction::class)->execute($basepath, $packages);

        file_put_contents($basepath.'/npm-audit.json', json_encode([
            'auditReportVersion' => 2,
            'vulnerabilities' => [
                'vite' => [
                    'name' => 'vite',
                    'severity' => 'high',
                    'isDirect' => true,
                    'via' => [
                        [
                            'source' => 2002,
                            'name' => 'vite',
                            'dependency' => 'vite',
                            'severity' => 'high',
                            'range' => '<7.0.2',
                        ],
                    ],
                    'range' => '<7.0.2',
                ],
            ],
        ], JSON_PRETTY_PRINT));

        $directFindings = app(DetectNpmVulnerabilitiesAction::class)->execute($basepath, $packages);

        expect($nestedFindings)->toHaveCount(1)
            ->and($nestedFindings[0]->toArray())->toMatchArray([
                'id' => 'npm-vite-2001',
                'package_name' => 'vite',
                'installed_version' => '6.0.0',
                'is_direct' => false,
                'recommendation' => 'Review which direct dependency requires vite before updating. Prefer updating the parent package rather than editing the lock file manually.',
                'suggested_command' => null,
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
    } finally {
        unlink($basepath.'/npm-audit.json');
        unlink($basepath.'/package-lock.json');
        unlink($basepath.'/package.json');
        rmdir($basepath);
    }
});

it('returns an empty list when npm audit output is missing', function (): void {
    $findings = app(DetectNpmVulnerabilitiesAction::class)->execute(
        __DIR__.'/../Fixtures/missing-project',
        [],
    );

    expect($findings)->toBe([]);
});
