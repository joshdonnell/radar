<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
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
#[UseFactory(RadarScanFactory::class)]
final class RadarScan extends Model
{
    /** @use HasFactory<RadarScanFactory> */
    use HasFactory;

    use HasUuids;
    use MassPrunable;

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
     * Get the current connection name for the model.
     */
    public function getConnectionName()
    {
        $connection = config('radar.storage.database.connection');

        return is_string($connection) ? $connection : parent::getConnectionName();
    }

    /** @return Builder<static> */
    public function prunable(): Builder
    {
        $configuredDays = config('radar.prune.days', 30);
        $days = false;

        if (is_int($configuredDays) || is_string($configuredDays)) {
            $days = filter_var($configuredDays, FILTER_VALIDATE_INT);
        }

        if (! is_int($days) || $days <= 0) {
            return self::query()->whereRaw('1 = 0');
        }

        return self::query()->where('created_at', '<', now()->subDays($days));
    }

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
