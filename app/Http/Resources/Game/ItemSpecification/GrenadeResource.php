<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'grenade',
    title: 'Grenade',
    description: 'Hand-thrown grenade specs sourced from Item.stdItem.Grenade.',
    properties: [
        new OA\Property(
            property: 'area_of_effect_min',
            description: 'Minimum lethal radius in meters.',
            type: 'double',
            example: 4.0,
            nullable: true
        ),
        new OA\Property(
            property: 'area_of_effect_max',
            description: 'Maximum lethal radius in meters.',
            type: 'double',
            example: 5.5,
            nullable: true
        ),
        new OA\Property(
            property: 'damage_type',
            description: 'Damage type reported by the grenade payload.',
            type: 'string',
            example: 'Physical',
            nullable: true
        ),
        new OA\Property(
            property: 'damage',
            description: 'Damage value used by the grenade payload.',
            type: 'double',
            example: 20.0,
            nullable: true
        ),
        new OA\Property(property: 'area_of_effect', type: 'double', nullable: true, deprecated: true),
    ],
    type: 'object'
)]
class GrenadeResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $grenade = Arr::get($this->extractStdItem($data), 'Grenade', []);

        $areaOfEffectMax = Arr::get($grenade, 'AreaOfEffect');

        return [
            'area_of_effect_min' => Arr::get($grenade, 'MinAreaOfEffect'),
            'area_of_effect_max' => $areaOfEffectMax,
            'damage_type' => Arr::get($grenade, 'DamageType'),
            'damage' => Arr::get($grenade, 'Damage'),
            // Backward compatibility with v2
            'area_of_effect' => $areaOfEffectMax,
        ];
    }
}
