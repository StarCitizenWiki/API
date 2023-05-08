<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Weapon\WeaponDamageResource;
use App\Http\Resources\SC\Weapon\WeaponModeResource;
use Illuminate\Http\Request;

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
    public function toArray($request): array
    {
        return [
            'class' => $this->weapon_class,
            'capacity' => $this->capacity ?? null,
            'range' => $this->ammunition->range ?? null,
            'damage_per_shot' => $this->ammunition->damage ?? null,
            'modes' => WeaponModeResource::collection($this->whenLoaded('modes')),
            'damages' => WeaponDamageResource::collection($this->whenLoaded('damages')),
            'ammunition' => [
                'size' => $this->ammunition->size ?? null,
                'lifetime' => $this->ammunition->lifetime ?? null,
                'speed' => $this->ammunition->speed ?? null,
                'range' => $this->ammunition->range ?? null,
            ],
            'updated_at' => $this->updated_at,
            'version' => $this->version,
        ];
    }
}
