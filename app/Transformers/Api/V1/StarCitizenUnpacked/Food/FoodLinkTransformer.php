<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Food;

use App\Models\StarCitizenUnpacked\Food\Food;
use App\Transformers\Api\V1\StarCitizenUnpacked\AbstractCommodityTransformer;

class FoodLinkTransformer extends AbstractCommodityTransformer
{
    /**
     * @param Food $food
     *
     * @return array
     */
    public function transform(Food $food): array
    {
        return [
            'name' => $food->item->name,
            'uuid' => $food->item->uuid,
            'link' => $this->makeApiUrl(self::UNPACKED_FOOD_SHOW, $food->getRouteKey()),
        ];
    }
}
