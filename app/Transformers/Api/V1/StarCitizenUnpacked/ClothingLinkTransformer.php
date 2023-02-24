<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\Clothing;

class ClothingLinkTransformer extends AbstractCommodityTransformer
{
    /**
     * @param Clothing $clothing
     *
     * @return array
     */
    public function transform(Clothing $clothing): array
    {
        return [
            'name' => $clothing->item->name,
            'uuid' => $clothing->item->uuid,
            'link' => $this->makeApiUrl(self::UNPACKED_CLOTHING_SHOW, $clothing->getRouteKey()),
        ];
    }
}
