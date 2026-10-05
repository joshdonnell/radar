<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Concerns;

use JoshDonnell\Radar\Enums\DependencyType;

trait ReadsJsonFiles
{
    /** @return array<string, mixed> */
    private function readJson(string $path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @param  array<string, string>  $dependencies
     * @return array<string, DependencyType>
     */
    private function directDependencies(array $dependencies, DependencyType $dependencyType): array
    {
        return array_fill_keys(array_keys($dependencies), $dependencyType);
    }

    /**
     * @param  array<string, string>  $dependencies
     * @return array<string, DependencyType>
     */
    private function composerDirectDependencies(array $dependencies, DependencyType $dependencyType): array
    {
        unset($dependencies['php']);

        foreach (array_keys($dependencies) as $package) {
            if (str_starts_with($package, 'ext-')) {
                unset($dependencies[$package]);
            }
        }

        return $this->directDependencies($dependencies, $dependencyType);
    }
}
