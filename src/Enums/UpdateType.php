<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Enums;

enum UpdateType: string
{
    case Patch = 'patch';
    case Minor = 'minor';
    case Major = 'major';
    case Unknown = 'unknown';
}
