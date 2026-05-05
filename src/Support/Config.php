<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Support;

use JoshDonnell\Radar\Exceptions\InvalidConfig;
use JoshDonnell\Radar\Http\Middleware\Authorize;

final class Config
{
    /**
     * @param  class-string  $defaultClass
     * @return class-string
     */
    public static function getActionClass(string $actionName, string $defaultClass): string
    {
        $actionClass = config('radar.actions.'.$actionName, $defaultClass);

        if (! is_string($actionClass) || ! class_exists($actionClass) || ! method_exists($actionClass, 'execute')) {
            throw InvalidConfig::invalidAction($actionName, $actionClass);
        }

        return $actionClass;
    }

    public static function databaseConnection(): ?string
    {
        $connection = config('radar.storage.database.connection');

        return is_string($connection) ? $connection : null;
    }

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

        return (string) mb_trim($path, '/');
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
