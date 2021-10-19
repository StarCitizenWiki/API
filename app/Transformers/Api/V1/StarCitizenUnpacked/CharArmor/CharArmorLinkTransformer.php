<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class CharArmorLinkTransformer extends AbstractCommodityTransformer
{
    /**
     * @param CharArmor $armor
     *
     * @return array
     */
    public function transform(CharArmor $armor): array
    {
        return [
            'name' => $armor->item->name,
            'uuid' => $armor->item->uuid,
            'link' => $this->makeApiUrl(self::UNPACKED_CHAR_ARMOR_SHOW, $armor->getRouteKey()),
        ];
    }
}
