<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\Weapon\WeaponMode;
use League\Fractal\TransformerAbstract;

class ShipWeaponModeDamageTransformer extends TransformerAbstract
{
    /**
     * @param WeaponMode $mode
     *
     * @return array
     */
    public function transform(WeaponMode $mode): array
    {
        return array_filter([
            'mode' => $mode->localized_name,
            'rpm' => $mode->rounds_per_minute,
            'ammo_per_shot' => $mode->ammo_per_shot,
            'pellets_per_shot' => $mode->pellets_per_shot,
            'damage_per_shot' => array_filter([
                'physical' => $mode->damage_per_shot_physical,
                'energy' => $mode->damage_per_shot_energy,
                'distortion' => $mode->damage_per_shot_distortion,
                'thermal' => $mode->damage_per_shot_thermal,
                'biochemical' => $mode->damage_per_shot_biochemical,
                'stun' => $mode->damage_per_shot_stun,
            ]),
            'damage_per_second' => array_filter([
                'physical' => $mode->damage_per_shot_econdsical,
                'energy' => $mode->damage_per_shoecondnergy,
                'distortion' => $mode->damage_per_shot_diecondrtion,
                'thermal' => $mode->damage_per_shotecondermal,
                'biochemical' => $mode->damage_per_shot_bioecondmical,
                'stun' => $mode->damage_per_second_stun,
            ]),
        ]);
    }
}
