<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => true);

    $manifest = json_decode((string) file_get_contents(__DIR__.'/../../../resources/dist/manifest.json'), true);

    $this->entry = $manifest['resources/js/app.ts'];
});

it('renders asset tags pointing at the radar asset route', function (): void {
    $script = basename($this->entry['file']);
    $stylesheet = basename($this->entry['css'][0]);

    $this->get('/radar')
        ->assertOk()
        ->assertSee("<script type=\"module\" src=\"/radar/assets/{$script}\"></script>", false)
        ->assertSee("<link rel=\"stylesheet\" href=\"/radar/assets/{$stylesheet}\">", false)
        ->assertDontSee('vendor/radar');
});

it('serves built assets with long lived cache headers', function (string $key, string $contentType): void {
    $file = basename(is_array($this->entry[$key]) ? $this->entry[$key][0] : $this->entry[$key]);

    $response = $this->get("/radar/assets/{$file}")->assertOk();

    expect($response->headers->get('Content-Type'))->toBe($contentType)
        ->and($response->headers->get('Cache-Control'))->toContain('immutable')
        ->and($response->headers->get('Cache-Control'))->toContain('max-age=31536000');
})->with([
    'script' => ['file', 'text/javascript; charset=utf-8'],
    'stylesheet' => ['css', 'text/css; charset=utf-8'],
    'font' => ['assets', 'font/woff2'],
]);

it('returns not found for unknown assets', function (string $file): void {
    $this->get("/radar/assets/{$file}")->assertNotFound();
})->with([
    'missing file' => 'missing.js',
    'unsupported extension' => 'manifest.json',
    'parent directory' => '..',
    'traversal' => '..%2Fmanifest.json',
]);

it('requires the configured gate to serve assets', function (): void {
    Gate::define('viewRadar', fn (?Authenticatable $user): bool => false);

    $this->get('/radar/assets/'.basename($this->entry['file']))->assertForbidden();
});

it('serves the dashboard from the vite dev server when it is running', function (): void {
    $hotFile = __DIR__.'/../../../resources/dist/hot';

    file_put_contents($hotFile, 'http://localhost:5173');

    try {
        $this->get('/radar')
            ->assertOk()
            ->assertSee('<script type="module" src="http://localhost:5173/@vite/client"></script>', false)
            ->assertSee('<script type="module" src="http://localhost:5173/resources/js/app.ts"></script>', false);
    } finally {
        unlink($hotFile);
    }
});

it('ignores the host application hot file', function (): void {
    file_put_contents(public_path('hot'), 'http://localhost:5173');

    try {
        $this->get('/radar')
            ->assertOk()
            ->assertDontSee('localhost:5173');
    } finally {
        unlink(public_path('hot'));
    }
});
