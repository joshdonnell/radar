<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Enums;

enum NodeRunner: string
{
    case Npm = 'npm';
    case Yarn = 'yarn';
    case Pnpm = 'pnpm';
    case Bun = 'bun';

    public static function fromLockFile(string $filename): self
    {
        return match ($filename) {
            'yarn.lock' => self::Yarn,
            'pnpm-lock.yaml' => self::Pnpm,
            'bun.lock', 'bun.lockb' => self::Bun,
            default => self::Npm,
        };
    }

    public static function fromProjectPath(string $basepath): self
    {
        foreach (['bun.lock', 'bun.lockb', 'pnpm-lock.yaml', 'yarn.lock', 'package-lock.json', 'npm-shrinkwrap.json'] as $filename) {
            if (file_exists($basepath.'/'.$filename)) {
                return self::fromLockFile($filename);
            }
        }

        return self::Npm;
    }

    public function updateCommand(string $packageName): string
    {
        return match ($this) {
            self::Npm => sprintf('npm update %s', $packageName),
            self::Yarn => sprintf('yarn up %s', $packageName),
            self::Pnpm => sprintf('pnpm update %s', $packageName),
            self::Bun => sprintf('bun update %s', $packageName),
        };
    }
}
