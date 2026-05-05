<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Enums;

enum Ecosystem: string
{
    case Composer = 'composer';
    case Npm = 'npm';
}
