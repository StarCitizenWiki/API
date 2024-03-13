<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Ammunition;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ammunition_v2',
    title: 'Ammunition',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', nullable: true),
        new OA\Property(property: 'size', type: 'double', nullable: true),
        new OA\Property(property: 'lifetime', type: 'double', nullable: true),
        new OA\Property(property: 'speed', type: 'double', nullable: true),
        new OA\Property(property: 'range', type: 'double', nullable: true),
        new OA\Property(property: 'piercability', ref: '#/components/schemas/ammunition_piercability_v2', nullable: true),
        new OA\Property(
            property: 'damage_falloffs',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ammunition_damage_falloff_v2'),
            nullable: true,
        ),
    ],
    type: 'object'
)]
class AmmunitionResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid ?? null,
            'size' => $this->size ?? null,
            'lifetime' => $this->lifetime ?? null,
            'speed' => $this->speed ?? null,
            'range' => $this->range ?? null,
            'piercability' => new AmmunitionPiercabilityResource($this->piercability),
            'damage_falloffs' => [
                'min_distance' => new AmmunitionDamageFalloffResource($this->damageFalloffs()->where('type', 'min_distance')->first() ?? []),
                'per_meter' => new AmmunitionDamageFalloffResource($this->damageFalloffs()->where('type', 'per_meter')->first() ?? []),
                'min_damage' => new AmmunitionDamageFalloffResource($this->damageFalloffs()->where('type', 'min_damage')->first() ?? []),
            ],
        ];
    }
}
