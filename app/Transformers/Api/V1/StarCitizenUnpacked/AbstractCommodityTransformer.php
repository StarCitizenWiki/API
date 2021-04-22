<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\Item;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use League\Fractal\Resource\Collection;

abstract class AbstractCommodityTransformer extends AbstractTranslationTransformer
{
    public function includeShops(Item $item): Collection
    {
        return $this->collection($item->shops, new ShopTransformer());
    }
}
