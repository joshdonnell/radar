<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Enums;

enum ScanCheck: string
{
    case Inventory = 'inventory';
    case Vulnerabilities = 'vulnerabilities';
    case Outdated = 'outdated';
}
