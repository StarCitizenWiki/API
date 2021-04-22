<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\Item;

class ItemTransformer extends AbstractCommodityTransformer
{
    protected $availableIncludes = [
        'shops'
    ];

    public function transform(Item $item): array
    {
        return [
            'uuid' => $item->uuid,
            'name' => $item->name,
            'type' => $item->type,
            'sub_type' => $item->sub_type,
            'manufacturer' => $item->manufacturer,
            'size' => $item->size,
        ];
    }
}
