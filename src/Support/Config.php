<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use JoshDonnell\Radar\Http\Middleware\Authorize;

final class Config
{
    public static function authorizationGate(): string
    {
        $gate = config('radar.authorization.gate', 'viewRadar');

        return is_string($gate) ? $gate : 'viewRadar';
    }

    public static function path(): string
    {
        $path = config('radar.path', 'radar');

        if (! is_string($path)) {
            return 'radar';
        }

        return trim($path, '/');
    }

    /**
     * @return array<int, string>
     */
    public static function routeMiddleware(): array
    {
        $middleware = config('radar.middleware', ['web']);

        if (! is_array($middleware)) {
            return ['web', Authorize::class];
        }

        $middleware = array_values(array_filter($middleware, is_string(...)));

        if (! in_array(Authorize::class, $middleware, true)) {
            $middleware[] = Authorize::class;
        }

        return $middleware;
    }
}
