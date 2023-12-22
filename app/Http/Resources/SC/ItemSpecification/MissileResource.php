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
        new OA\Property(property: 'cluster_size', type: 'number', nullable: true),
        new OA\Property(property: 'signal_type', type: 'string', nullable: true),
        new OA\Property(property: 'lock_time', type: 'double', nullable: true),
        new OA\Property(property: 'lock_range_max', type: 'double', nullable: true),
        new OA\Property(property: 'lock_range_min', type: 'double', nullable: true),
        new OA\Property(property: 'lock_angle', type: 'double', nullable: true),
        new OA\Property(property: 'tracking_signal_min', type: 'double', nullable: true),
        new OA\Property(property: 'speed', type: 'double', nullable: true),
        new OA\Property(property: 'fuel_tank_size', type: 'double', nullable: true),
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
            'cluster_size' => $this->cluster_size,
            'signal_type' => $this->getDescriptionDatum('Tracking Signal'),
            'lock_time' => $this->lock_time,
            'lock_range_max' => $this->lock_range_max,
            'lock_range_min' => $this->lock_range_min,
            'lock_angle' => $this->lock_angle,
            'tracking_signal_min' => $this->tracking_signal_min,
            'speed' => $this->speed,
            'fuel_tank_size' => $this->fuel_tank_size,
            'explosion_radius_min' => $this->explosion_radius_min,
            'explosion_radius_max' => $this->explosion_radius_max,
            'damage_total' => $this->damage ?? 0,
            'damages' => WeaponDamageResource::collection($this->whenLoaded('damages')),
        ];
    }
}
