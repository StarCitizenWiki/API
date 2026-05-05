<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'grenade_aoe',
    title: 'Grenade Area of Effect',
    description: 'Area of effect radii in meters.',
    properties: [
        new OA\Property(
            property: 'min',
            description: 'Minimum effective/lethal radius in meters.',
            type: 'double',
            example: 4.0,
            nullable: true
        ),
        new OA\Property(
            property: 'max',
            description: 'Maximum effective/lethal radius in meters.',
            type: 'double',
            example: 5.5,
            nullable: true
        ),
        new OA\Property(
            property: 'minimum',
            description: 'Deprecated: Use min.',
            type: 'double',
            example: 4.0,
            nullable: true,
            deprecated: true
        ),
        new OA\Property(
            property: 'maximum',
            description: 'Deprecated: Use max.',
            type: 'double',
            example: 5.5,
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'grenade',
    title: 'Grenade',
    description: 'Hand-thrown grenade specs sourced from Item.stdItem.Grenade.',
    properties: [
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
        new OA\Property(
            property: 'aoe',
            ref: '#/components/schemas/grenade_aoe',
            description: 'Preferred area-of-effect representation.'
        ),
        new OA\Property(
            property: 'area_of_effect',
            description: 'Deprecated. Use `aoe.maximum`.',
            type: 'double',
            nullable: true,
            deprecated: true
        ),
    ],
    type: 'object'
)]
class GrenadeResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $grenade = Arr::get($stdItem, 'Grenade', []);

        $areaOfEffectMax = Arr::get($grenade, 'AreaOfEffect');

        return [

            'damage_type' => Arr::get($grenade, 'DamageType'),
            'damage' => Arr::get($grenade, 'Damage'),

            'aoe' => [
                'min' => Arr::get($grenade, 'MinAreaOfEffect'),
                'max' => $areaOfEffectMax,
                'minimum' => Arr::get($grenade, 'MinAreaOfEffect'),  // deprecated: use min
                'maximum' => $areaOfEffectMax,  // deprecated: use max
            ],

            'area_of_effect' => $areaOfEffectMax,
        ];
    }
}
