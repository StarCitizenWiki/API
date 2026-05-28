<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'self_destruct',
    title: 'Self-Destruct Module',
    description: 'Self-destruct charge parameters.',
    properties: [
        new OA\Property(
            property: 'damage',
            description: 'Peak blast damage at the center of the explosion.',
            type: 'double',
            example: 15000,
            nullable: true
        ),
        new OA\Property(
            property: 'radius',
            description: 'Maximum damage radius in meters.',
            type: 'double',
            example: 80,
            nullable: true,
            x: ['suffix' => ' m']
        ),
        new OA\Property(
            property: 'min_radius',
            description: 'Inner radius in meters that always receives full damage before falloff begins.',
            type: 'double',
            example: 30,
            nullable: true,
            x: ['suffix' => ' m']
        ),
        new OA\Property(
            property: 'phys_radius',
            description: 'Physical impact radius in meters.',
            type: 'double',
            example: 50,
            nullable: true,
            x: ['suffix' => ' m']
        ),
        new OA\Property(
            property: 'min_phys_radius',
            description: 'Inner physical impact radius in meters.',
            type: 'double',
            example: 30,
            nullable: true,
            x: ['suffix' => ' m']
        ),
        new OA\Property(
            property: 'time',
            description: 'Countdown duration in seconds before detonation once armed.',
            type: 'double',
            example: 45,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'countdown',
            description: 'Countdown duration in seconds before detonation.',
            type: 'double',
            example: 20,
            nullable: true,
            x: ['suffix' => ' s']
        ),
    ],
    type: 'object'
)]
class SelfDestructResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);

        $selfDestruct = Arr::get($data, 'stdItem.SelfDestruct', []);

        $time = Arr::get($selfDestruct, 'Time');

        return [
            'damage' => Arr::get($selfDestruct, 'Damage'),
            'radius' => Arr::get($selfDestruct, 'Radius'),
            'min_radius' => Arr::get($selfDestruct, 'MinRadius'),
            'phys_radius' => Arr::get($selfDestruct, 'PhysRadius'),
            'min_phys_radius' => Arr::get($selfDestruct, 'MinPhysRadius'),
            'time' => $time,
            'countdown' => $time,
        ];
    }
}
