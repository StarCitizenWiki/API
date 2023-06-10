<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle\Weapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon_regen_v2',
    title: 'Vehicle Weapon Regen',
    properties: [
        new OA\Property(property: 'requested_regen_per_sec', type: 'double', nullable: true),
        new OA\Property(property: 'requested_ammo_load', type: 'double', nullable: true),
        new OA\Property(property: 'cooldown', type: 'double', nullable: true),
        new OA\Property(property: 'cost_per_bullet', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class VehicleWeaponRegenResource extends AbstractBaseResource
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
            'requested_regen_per_sec' => $this->requested_regen_per_sec,
            'requested_ammo_load' => $this->requested_ammo_load,
            'cooldown' => $this->cooldown,
            'cost_per_bullet' => $this->cost_per_bullet,
        ];
    }
}
