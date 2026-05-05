<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Support\Formatting\FormatDuration;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_lifetime',
    title: 'Mission Lifetime',
    properties: [
        new OA\Property(property: 'label', type: 'string', nullable: true),
        new OA\Property(property: 'respawn_time_seconds', type: 'integer'),
        new OA\Property(property: 'max_instances', type: 'integer'),
        new OA\Property(property: 'respawn_time_variation_seconds', type: 'integer'),
        new OA\Property(property: 'max_instances_per_player', type: 'integer'),
    ],
    type: 'object'
)]
class MissionLifetimeResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $lifetime = $this->resource;
        $respawnTimeMinutes = $lifetime['RespawnTime'] ?? null;
        $respawnTimeSeconds = is_numeric($respawnTimeMinutes) ? (int) $respawnTimeMinutes * 60 : null;

        return [
            'label' => $respawnTimeSeconds !== null && $respawnTimeSeconds > 0
                ? FormatDuration::fromSeconds($respawnTimeSeconds)
                : null,
            'respawn_time_seconds' => $respawnTimeSeconds,
            'max_instances' => $lifetime['MaxInstances'] ?? null,
            'respawn_time_variation_seconds' => isset($lifetime['RespawnTimeVariation']) && is_numeric($lifetime['RespawnTimeVariation'])
                ? (int) $lifetime['RespawnTimeVariation'] * 60
                : null,
            'max_instances_per_player' => $lifetime['MaxInstancesPerPlayer'] ?? null,
        ];
    }
}
