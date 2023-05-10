<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Weapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_damage_v2',
    title: 'Weapon Damages',
    description: 'Weapon Damages',
    properties: [
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'damage', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class WeaponDamageResource extends AbstractBaseResource
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
            'type' => $this->type,
            'name' => $this->name,
            'damage' => $this->damage,
        ];
    }
}
