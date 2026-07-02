<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

final class Assets
{
    /**
     * Determine whether the published dashboard assets match the ones shipped
     * with the installed package. They drift out of sync when Radar is
     * upgraded but `vendor:publish --tag=radar-assets` has not been re-run.
     */
    public static function areCurrent(): bool
    {
        // The Vite dev server is running, so assets are served fresh.
        if (is_file(public_path('hot'))) {
            return true;
        }

        $published = public_path('vendor/radar/manifest.json');
        $packaged = self::packagedManifestPath();

        if (! is_file($published) || ! is_file($packaged)) {
            return false;
        }

        return md5_file($published) === md5_file($packaged);
    }

    private static function packagedManifestPath(): string
    {
        return dirname(__DIR__, 2).'/resources/dist/manifest.json';
    }
}
