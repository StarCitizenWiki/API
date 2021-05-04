<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Shop;

use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Transformers\Api\V1\AbstractV1Transformer;
use League\Fractal\Resource\Collection;

class ShopTransformer extends AbstractV1Transformer
{
    protected $availableIncludes = [
        'items'
    ];

    public function transform(Shop $shop): array
    {
        return [
            'uuid' => $shop->uuid,
            'name_raw' => $shop->name_raw,
            'name' => $shop->name,
            'position' => $shop->position,
            'profit_margin' => $shop->profit_margin,
            'version' => $shop->version,
        ];
    }

    public function includeItems(Shop $shop): Collection
    {
        return $this->collection($shop->items, new ShopItemTransformer());
    }
}
