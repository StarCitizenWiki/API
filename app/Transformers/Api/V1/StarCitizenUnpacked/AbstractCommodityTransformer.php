<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\CommodityItem;
use App\Models\StarCitizenUnpacked\Item;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use League\Fractal\Resource\Collection;

abstract class AbstractCommodityTransformer extends AbstractTranslationTransformer
{
    /**
     * @param Item|CommodityItem $item
     * @return Collection
     */
    public function includeShops($item): Collection
    {
        return $this->collection($item->shops, new ShopTransformer());
    }
}
