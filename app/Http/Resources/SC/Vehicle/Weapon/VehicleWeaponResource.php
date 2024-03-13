<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle\Weapon;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Ammunition\AmmunitionResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use App\Http\Resources\SC\Weapon\WeaponModeResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'vehicle_weapon_v2',
    title: 'Vehicle Weapon',
    properties: [
        new OA\Property(property: 'class', type: 'string', nullable: true),
        new OA\Property(property: 'capacity', type: 'double', nullable: true),
        new OA\Property(property: 'range', type: 'string', nullable: true),
        new OA\Property(property: 'damage_per_shot', type: 'double', nullable: true),
        new OA\Property(
            property: 'modes',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_mode_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'damages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_v2'),
            nullable: true
        ),
        new OA\Property(
            property: 'regeneration',
            ref: '#/components/schemas/vehicle_weapon_regen_v2',
            nullable: true
        ),
        new OA\Property(property: 'ammunition', ref: '#/components/schemas/ammunition_v2', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', nullable: true),
        new OA\Property(property: 'version', type: 'string', nullable: true),
    ],
    type: 'object'
)]
class VehicleWeaponResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'modes',
            'damages',
            'ports',
            'shops',
            'shops.items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'class' => $this->weapon_class,
            'type' => $this->weapon_type,
            'capacity' => $this->capacity ?? null,
            'range' => $this->ammunition->range ?? null,
            'damage_per_shot' => $this->ammunition->damage ?? null,
            'modes' => WeaponModeResource::collection($this->whenLoaded('modes')),
            'damages' => WeaponDamageResource::collection($this->damages()),
            'regeneration' => new VehicleWeaponRegenResource($this->whenLoaded('regen')),
            'ammunition' => new AmmunitionResource($this->ammunition),
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];
    }
}
