<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Missile;
use App\Models\StarCitizenUnpacked\ShipItem\Weapon\Weapon;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class WeaponTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'modes',
        'damages',
    ];

    /**
     * @param Weapon $weapon
     * @return array
     */
    public function transform($weapon): array
    {
        if ($weapon instanceof Missile) {
            $this->defaultIncludes = ['damages'];
        }

        return [
            'speed' => $weapon->speed,
            'range' => $weapon->range,
            'size' => $weapon->size,
            'capacity' => $weapon->capacity,
            'damage_per_shot' => $weapon->damage ?? 0,
        ];
    }

    public function includeDamages($weapon): Collection
    {
        return $this->collection($weapon->damages, new WeaponDamageTransformer());
    }

    public function includeModes($weapon): Collection
    {
        return $this->collection($weapon->modes, new WeaponModeTransformer());
    }
}
