<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Enums;

enum DependencyType: string
{
    case Production = 'production';
    case Development = 'development';
    case Peer = 'peer';
}
