<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Enums;

enum OutdatedColumn: string
{
    case Ecosystem = 'Ecosystem';
    case Package = 'Package';
    case Current = 'Current';
    case Latest = 'Latest';
    case Type = 'Type';
    case Dependency = 'Dependency';
}
