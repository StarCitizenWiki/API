<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAmmunition;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class WeaponPersonalAmmunitionTransformer extends AbstractTranslationTransformer
{
    /**
     * @param WeaponPersonalAmmunition $ammunition
     *
     * @return array
     */
    public function transform(WeaponPersonalAmmunition $ammunition): array
    {
        return [
            'size' => $ammunition->size,
            'lifetime' => $ammunition->lifetime,
            'speed' => $ammunition->speed,
            'range' => $ammunition->range,
        ];
    }
}
