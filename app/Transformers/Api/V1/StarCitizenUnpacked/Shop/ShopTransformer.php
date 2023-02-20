<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Shop;

use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Transformers\Api\V1\AbstractV1Transformer;
use League\Fractal\Resource\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shop',
    title: 'Shop',
    description: 'An in-game Shop',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name_raw', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'position', type: 'string'),
        new OA\Property(property: 'profit_margin', type: 'float'),
        new OA\Property(property: 'version', type: 'string'),
        new OA\Property(
            property: 'items',
            properties: [
                new OA\Property(
                    property: 'items',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/item',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
        ),
    ],
    type: 'object'
)]
class ShopTransformer extends AbstractV1Transformer
{
    protected array $availableIncludes = [
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
