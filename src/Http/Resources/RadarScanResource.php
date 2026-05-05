<?php

declare(strict_types=1);

namespace JoshDonnell\Radar\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JoshDonnell\Radar\Models\RadarScan;

/** @mixin RadarScan */
final class RadarScanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'score' => $this->score,
            'package_count' => $this->package_count,
            'vulnerability_count' => $this->vulnerability_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}
