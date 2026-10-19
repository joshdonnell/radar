<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Controllers;

use JoshDonnell\Radar\Support\Assets;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class AssetController
{
    public function __invoke(string $file): BinaryFileResponse
    {
        $path = Assets::path($file);

        abort_if($path === null, 404);

        return response()->file($path, [
            'Content-Type' => Assets::contentType($file),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
