<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Weapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_mode_v2',
    title: 'Weapon Modes',
    description: 'Weapon Fire Modes',
    properties: [
        new OA\Property(property: 'mode', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'rpm', type: 'double', nullable: true),
        new OA\Property(property: 'ammo_per_shot', type: 'double', nullable: true),
        new OA\Property(property: 'pellets_per_shot', type: 'double', nullable: true),
        new OA\Property(property: 'damage_per_second', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class WeaponModeResource extends AbstractBaseResource
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
            'mode' => $this->mode,
            'type' => $this->type,
            'rpm' => $this->rounds_per_minute,
            'ammo_per_shot' => $this->ammo_per_shot,
            'pellets_per_shot' => $this->pellets_per_shot,
            'damage_per_second' => $this->damagePerSecond,
        ];
    }
}
