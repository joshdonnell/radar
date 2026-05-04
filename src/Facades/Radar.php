<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JoshDonnell\Radar\Radar
 */
final class Radar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JoshDonnell\Radar\Radar::class;
    }
}
