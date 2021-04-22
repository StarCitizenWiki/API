<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked\Shop;

use App\Models\StarCitizenUnpacked\Item;
use League\Fractal\TransformerAbstract;

class ShopItemTransformer extends TransformerAbstract
{
    public function transform(Item $item): array
    {
        return [
            'uuid' => $item->uuid,
            'name' => $item->name,
            'base_price' => $item->shop_data->base_price,
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
            'version' => config('api.sc_data_version'),
        ];
    }
}
