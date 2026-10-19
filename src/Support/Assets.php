<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use Illuminate\Support\HtmlString;

final class Assets
{
    private const string Entry = 'resources/js/app.ts';

    /** @var array<string, string> */
    private const array ContentTypes = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'text/javascript; charset=utf-8',
        'woff2' => 'font/woff2',
    ];

    /**
     * Assets are served straight from the package through a Radar route rather
     * than published into the host app, so they can never drift out of date
     * after an upgrade and never collide with the host app's own Vite setup.
     */
    public static function tags(): HtmlString
    {
        $devServerUrl = self::devServerUrl();

        if ($devServerUrl !== null) {
            return new HtmlString(implode("\n", [
                self::scriptTag("{$devServerUrl}/@vite/client"),
                self::scriptTag("{$devServerUrl}/".self::Entry),
            ]));
        }

        $entry = self::manifest()[self::Entry] ?? null;

        if (! is_array($entry)) {
            return new HtmlString('');
        }

        $stylesheets = is_array($entry['css'] ?? null) ? $entry['css'] : [];

        $tags = array_map(
            static fn (mixed $file): string => is_string($file)
                ? '<link rel="stylesheet" href="'.e(self::url($file)).'">'
                : '',
            $stylesheets,
        );

        if (is_string($entry['file'] ?? null)) {
            $tags[] = self::scriptTag(self::url($entry['file']));
        }

        return new HtmlString(implode("\n", array_filter($tags)));
    }

    public static function path(string $file): ?string
    {
        if ($file !== basename($file)) {
            return null;
        }

        if (self::contentType($file) === null) {
            return null;
        }

        $path = self::distPath("assets/{$file}");

        return is_file($path) ? $path : null;
    }

    public static function contentType(string $file): ?string
    {
        return self::ContentTypes[pathinfo($file, PATHINFO_EXTENSION)] ?? null;
    }

    private static function url(string $file): string
    {
        return route('radar.assets', ['file' => basename($file)], false);
    }

    private static function scriptTag(string $src): string
    {
        return '<script type="module" src="'.e($src).'"></script>';
    }

    private static function devServerUrl(): ?string
    {
        $hotFile = self::distPath('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = mb_rtrim((string) file_get_contents($hotFile));

        return $url !== '' ? mb_rtrim($url, '/') : null;
    }

    /** @return array<mixed> */
    private static function manifest(): array
    {
        $manifestPath = self::distPath('manifest.json');

        if (! is_file($manifestPath)) {
            return [];
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);

        return is_array($manifest) ? $manifest : [];
    }

    private static function distPath(string $path): string
    {
        return dirname(__DIR__, 2)."/resources/dist/{$path}";
    }
}
