<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalMode;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;

class WeaponPersonalModeTransformer extends AbstractTranslationTransformer
{
    protected $defaultIncludes = [
        'damages'
    ];

    /**
     * @param WeaponPersonalMode $mode
     *
     * @return array
     */
    public function transform(WeaponPersonalMode $mode): array
    {
        return [
            'mode' => $mode->mode,
            'type' => $mode->type,
            'rpm' => $mode->rounds_per_minute,
            'ammo_per_shot' => $mode->ammo_per_shot,
            'pellets_per_shot' => $mode->pellets_per_shot,
            'damage_per_second' => $mode->damagePerSecond,
        ];
    }

    public function includeDamages(WeaponPersonalMode $mode): Collection
    {
        return $this->collection($mode->weapon->ammunition->damages, new WeaponPersonalAmmunitionDamageTransformer());
    }
}
