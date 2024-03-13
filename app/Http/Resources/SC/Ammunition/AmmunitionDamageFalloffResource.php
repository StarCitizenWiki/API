<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Ammunition;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ammunition_damage_falloff_v2',
    title: 'Ammunition Damage Falloff',
    properties: [
        new OA\Property(property: 'physical', type: 'double', nullable: true),
        new OA\Property(property: 'energy', type: 'double', nullable: true),
        new OA\Property(property: 'distortion', type: 'double', nullable: true),
        new OA\Property(property: 'thermal', type: 'double', nullable: true),
        new OA\Property(property: 'biochemical', type: 'double', nullable: true),
        new OA\Property(property: 'stun', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class AmmunitionDamageFalloffResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {

        return [
            'physical' => $this->physical ?? null,
            'energy' => $this->energy ?? null,
            'distortion' => $this->distortion ?? null,
            'thermal' => $this->thermal ?? null,
            'biochemical' => $this->biochemical ?? null,
            'stun' => $this->stun ?? null,
        ];
    }
}
