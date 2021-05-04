<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAmmunitionDamage;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

class WeaponPersonalAmmunitionDamageTransformer extends AbstractTranslationTransformer
{
    /**
     * @param WeaponPersonalAmmunitionDamage $mode
     *
     * @return array
     */
    public function transform(WeaponPersonalAmmunitionDamage $mode): array
    {
        return [
            'type' => $mode->type,
            'name' => $mode->name,
            'damage' => $mode->damage,
        ];
    }
}
