<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Char;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'personal_weapon_magazine_v2',
    title: 'Personal Weapon Magazine',
    description: 'Personal Weapon Magazine',
    properties: [
        new OA\Property(property: 'initial_ammo_count', type: 'integer', nullable: true),
        new OA\Property(property: 'max_ammo_count', type: 'integer', nullable: true),
    ],
    type: 'object'
)]
class PersonalWeaponMagazineResource extends AbstractBaseResource
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
            'initial_ammo_count' => $this->initial_ammo_count ?? null,
            'max_ammo_count' => $this->max_ammo_count ?? null,
        ];
    }
}
