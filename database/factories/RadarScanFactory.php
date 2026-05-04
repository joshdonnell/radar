<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JoshDonnell\Radar\Models\RadarScan;

final class RadarScanFactory extends Factory
{
    protected $model = RadarScan::class;

    public function definition(): array
    {
        return [
            'score' => fake()->numberBetween(0, 100),
            'vulnerability_count' => fake()->numberBetween(0, 50),
            'package_count' => fake()->numberBetween(1, 200),
            'payload' => [],
        ];
    }
}
