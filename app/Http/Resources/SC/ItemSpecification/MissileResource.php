<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'missile_v2',
    title: 'Missile',
    properties: [
        new OA\Property(property: 'signal_type', type: 'string', nullable: true),
        new OA\Property(property: 'lock_time', type: 'double', nullable: true),
        new OA\Property(property: 'damage_total', type: 'double', nullable: true),
        new OA\Property(
            property: 'damages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_v2')
        ),
    ],
    type: 'object'
)]
class MissileResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'signal_type' => $this->signal_type,
            'lock_time' => $this->lock_time,
            'damage_total' => $this->damage ?? 0,
            'damages' => WeaponDamageResource::collection($this->whenLoaded('damages')),
        ];
    }
}
