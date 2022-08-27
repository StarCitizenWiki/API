<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class WeaponPersonalLinkTransformer extends AbstractCommodityTransformer
{
    /**
     * @param WeaponPersonal $weaponPersonal
     *
     * @return array
     */
    public function transform(WeaponPersonal $weaponPersonal): array
    {
        return [
            'name' => $weaponPersonal->item->name,
            'uuid' => $weaponPersonal->item->uuid,
            'link' => $this->makeApiUrl(self::UNPACKED_WEAPON_PERSONAL_SHOW, $weaponPersonal->getRouteKey()),
        ];
    }
}
