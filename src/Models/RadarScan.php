<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use JoshDonnell\Radar\Database\Factories\RadarScanFactory;

/**
 * @property-read string $id
 * @property-read int|null $score
 * @property-read int $vulnerability_count
 * @property-read int $package_count
 * @property-read array<string, mixed> $payload
 * @property-read CarbonImmutable|null $created_at
 */
final class RadarScan extends Model
{
    /** @use HasFactory<RadarScanFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * The name of the "updated at" column.
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'score',
        'vulnerability_count',
        'package_count',
        'payload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'vulnerability_count' => 'integer',
            'package_count' => 'integer',
            'payload' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }
}
