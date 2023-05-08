<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Weapon;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

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
