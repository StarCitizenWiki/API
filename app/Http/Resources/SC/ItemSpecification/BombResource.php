<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'bomb_v2',
    title: 'Bomb',
    properties: [
        new OA\Property(property: 'arm_time', type: 'double', nullable: true),
        new OA\Property(property: 'ignite_time', type: 'double', nullable: true),
        new OA\Property(property: 'collision_delay_time', type: 'double', nullable: true),
        new OA\Property(property: 'explosion_safety_distance', type: 'double', nullable: true),
        new OA\Property(property: 'explosion_radius_min', type: 'double', nullable: true),
        new OA\Property(property: 'explosion_radius_max', type: 'double', nullable: true),
        new OA\Property(property: 'damage_total', type: 'double', nullable: true),
        new OA\Property(
            property: 'damages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_v2')
        ),
    ],
    type: 'object'
)]
class BombResource extends AbstractBaseResource
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
            'arm_time' => $this->arm_time,
            'ignite_time' => $this->ignite_time,
            'collision_delay_time' => $this->collision_delay_time,
            'explosion_safety_distance' => $this->explosion_safety_distance,
            'explosion_radius_min' => $this->explosion_radius_min,
            'explosion_radius_max' => $this->explosion_radius_max,
            'damage' => $this->damage ?? 0,
            'damages' => WeaponDamageResource::collection($this->whenLoaded('damages')),
        ];
    }
}
