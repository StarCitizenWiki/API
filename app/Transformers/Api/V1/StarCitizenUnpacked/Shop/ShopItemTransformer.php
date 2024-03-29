<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Shop;

use App\Models\StarCitizenUnpacked\Item;
use League\Fractal\TransformerAbstract;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shop_item',
    title: 'Shop Item',
    description: 'An item from an in-game Shop',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'sub_type', type: 'string'),
        new OA\Property(property: 'base_price', type: 'float'),
        new OA\Property(property: 'price_calculated', type: 'float'),
        new OA\Property(property: 'price_range', type: 'float'),
        new OA\Property(property: 'base_price_offset', type: 'float'),
        new OA\Property(property: 'max_discount', type: 'float'),
        new OA\Property(property: 'max_premium', type: 'float'),
        new OA\Property(property: 'inventory', type: 'float'),
        new OA\Property(property: 'optimal_inventory', type: 'float'),
        new OA\Property(property: 'max_inventory', type: 'float'),
        new OA\Property(property: 'auto_restock', type: 'boolean'),
        new OA\Property(property: 'auto_consume', type: 'boolean'),
        new OA\Property(property: 'refresh_rate', type: 'float'),
        new OA\Property(property: 'buyable', type: 'boolean'),
        new OA\Property(property: 'sellable', type: 'boolean'),
        new OA\Property(property: 'rentable', type: 'boolean'),
        new OA\Property(property: 'version', type: 'string'),
        new OA\Property(
            property: 'rental_price_days',
            properties: [
                new OA\Property(property: '1', type: 'float'),
                new OA\Property(property: '3', type: 'float'),
                new OA\Property(property: '7', type: 'float'),
                new OA\Property(property: '30', type: 'float'),
            ],
            nullable: true,
        ),
    ],
    type: 'object'
)]
class ShopItemTransformer extends TransformerAbstract
{
    public function transform(Item $item): array
    {
        $data = [
            'uuid' => $item->uuid,
            'name' => $item->name,
            'type' => $item->type,
            'sub_type' => $item->sub_type,
            'base_price' => $item->shop_data->base_price,
            'price_calculated' => $item->shop_data->offsetted_price,
            'price_range' => $item->shop_data->price_range,
            'base_price_offset' => $item->shop_data->base_price_offset,
            'max_discount' => $item->shop_data->max_discount,
            'max_premium' => $item->shop_data->max_premium,
            'inventory' => $item->shop_data->inventory,
            'optimal_inventory' => $item->shop_data->optimal_inventory,
            'max_inventory' => $item->shop_data->max_inventory,
            'auto_restock' => $item->shop_data->auto_restock,
            'auto_consume' => $item->shop_data->auto_consume,
            'refresh_rate' => $item->shop_data->refresh_rate,
            'buyable' => $item->shop_data->buyable,
            'sellable' => $item->shop_data->sellable,
            'rentable' => $item->shop_data->rentable,
            'version' => $item->shop_data->version,
        ];


        if (isset($item->shop_data->rental->id)) {
            $data['rental_price_days'] = [
                1 => $item->shop_data->price1,
                3 => $item->shop_data->price3,
                7 => $item->shop_data->price7,
                30 => $item->shop_data->price30,
            ];
        }

        return $data;
    }
}
