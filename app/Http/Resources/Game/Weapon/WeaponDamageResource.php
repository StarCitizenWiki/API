<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Weapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'weapon_damage',
    title: 'Weapon Damage',
    description: 'Individual damage type with value',
    properties: [
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'damage', type: 'double', nullable: true),
    ],
    type: 'object'
)]
class WeaponDamageResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => Arr::get($this->resource, 'type'),
            'name' => Arr::get($this->resource, 'name'),
            'damage' => Arr::get($this->resource, 'damage'),
        ];
    }
}
