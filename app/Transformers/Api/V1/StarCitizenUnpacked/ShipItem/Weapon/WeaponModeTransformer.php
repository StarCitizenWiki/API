<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\WeaponMode;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class WeaponModeTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'damages',
    ];

    /**
     * @param WeaponMode $mode
     *
     * @return array
     */
    public function transform(WeaponMode $mode): array
    {
        return array_filter([
            'mode' => $mode->mode,
            'type' => $mode->type,
            'rpm' => $mode->rounds_per_minute,
            'ammo_per_shot' => $mode->ammo_per_shot,
            'pellets_per_shot' => $mode->pellets_per_shot,
            'damage_per_second' => $mode->damagePerSecond,
        ]);
    }

    public function includeDamages(WeaponMode $weaponMode): Collection
    {
        return $this->collection($weaponMode->damages, new WeaponDamageTransformer());
    }
}
