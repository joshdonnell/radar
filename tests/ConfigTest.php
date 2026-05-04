<?php

declare(strict_types=1);

it('loads radar configuration defaults', function (): void {
    expect(config('radar'))
        ->toBeArray()
        ->and(config('radar.enabled'))->toBeTrue()
        ->and(config('radar.path'))->toBe('radar')
        ->and(config('radar.storage.database.connection'))->toBe('sqlite')
        ->and(config('radar.middleware'))->toBe(['web'])
        ->and(config('radar.authorization.gate'))->toBe('viewRadar')
        ->and(config('radar.notifications.mail.to'))->toBe([])
        ->and(config('radar.notifications.slack.webhook_url'))->toBeNull();
});
