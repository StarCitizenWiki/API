<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char\PersonalWeapon;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Ammunition\AmmunitionResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use App\Http\Resources\SC\Weapon\WeaponModeResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon_v2',
    title: 'Personal Weapon',
    description: 'Personal Weapon',
    properties: [
        new OA\Property(property: 'class', type: 'string', nullable: true),
        new OA\Property(property: 'magazine_type', type: 'string', nullable: true),
        new OA\Property(property: 'magazine_size', type: 'string', nullable: true),
        new OA\Property(property: 'effective_range', type: 'string', nullable: true),
        new OA\Property(property: 'damage_per_shot', type: 'double', nullable: true),
        new OA\Property(property: 'rof', type: 'string', nullable: true),
        new OA\Property(
            property: 'modes',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_mode_v2'),
            nullable: true,
        ),
        new OA\Property(
            property: 'damages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/weapon_damage_v2'),
            nullable: true,
        ),
        new OA\Property(property: 'ammunition', ref: '#/components/schemas/ammunition_v2', nullable: true),

    ],
    type: 'object'
)]
class PersonalWeaponResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return parent::validIncludes() + [
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
        $data = [
            'class' => $this->weapon_class,
            'type' => $this->weapon_type,
            'magazine_type' => $this->magazineType,
            'magazine_size' => $this->magazine->max_ammo_count ?? null,
            'effective_range' => $this->effective_range ?? null,
            'damage_per_shot' => $this->ammunition?->damage ?? null,
            'rof' => $this->rof ?? null,
            'modes' => WeaponModeResource::collection($this->whenLoaded('modes')),
            'damages' => WeaponDamageResource::collection($this->damages()),
        ];

        if ($this->sub_type !== 'Knife') {
            $data['ammunition'] = new AmmunitionResource($this->ammunition);
        }

        return $data;
    }
}
