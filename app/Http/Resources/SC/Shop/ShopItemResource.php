<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Shop;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shop_item_v2',
    title: 'Ship Item',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'sub_type', type: 'string'),
        new OA\Property(property: 'base_price', type: 'double'),
        new OA\Property(property: 'price_calculated', type: 'double'),
        new OA\Property(property: 'price_range', type: 'double'),
        new OA\Property(property: 'base_price_offset', type: 'double'),
        new OA\Property(property: 'max_discount', type: 'double'),
        new OA\Property(property: 'max_premium', type: 'double'),
        new OA\Property(property: 'inventory', type: 'double'),
        new OA\Property(property: 'optimal_inventory', type: 'double'),
        new OA\Property(property: 'max_inventory', type: 'double'),
        new OA\Property(property: 'auto_restock', type: 'boolean'),
        new OA\Property(property: 'auto_consume', type: 'boolean'),
        new OA\Property(property: 'refresh_rate', type: 'double'),
        new OA\Property(property: 'buyable', type: 'boolean'),
        new OA\Property(property: 'sellable', type: 'boolean'),
        new OA\Property(property: 'rentable', type: 'boolean'),
        new OA\Property(property: 'version', type: 'string'),
        new OA\Property(property: 'rental_price_days', properties: [
            new OA\Property(property: 'duration_1', type: 'double'),
            new OA\Property(property: 'duration_3', type: 'double'),
            new OA\Property(property: 'duration_7', type: 'double'),
            new OA\Property(property: 'duration_30', type: 'double'),
        ], type: 'object', nullable: true),
    ],
    type: 'object'
)]
class ShopItemResource extends AbstractBaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'type' => $this->cleanType(),
            'sub_type' => $this->sub_type,
            'base_price' => $this->shop_data->base_price,
            'price_calculated' => $this->shop_data->offsetted_price,
            'price_range' => $this->shop_data->price_range,
            'base_price_offset' => $this->shop_data->base_price_offset,
            'max_discount' => $this->shop_data->max_discount,
            'max_premium' => $this->shop_data->max_premium,
            'inventory' => $this->shop_data->inventory,
            'optimal_inventory' => $this->shop_data->optimal_inventory,
            'max_inventory' => $this->shop_data->max_inventory,
            'auto_restock' => $this->shop_data->auto_restock,
            'auto_consume' => $this->shop_data->auto_consume,
            'refresh_rate' => $this->shop_data->refresh_rate,
            'buyable' => $this->shop_data->buyable,
            'sellable' => $this->shop_data->sellable,
            'rentable' => $this->shop_data->rentable,
            $this->mergeWhen($this->shop_data->rentable === true, [
                'rental_price_days' => [
                    'duration_1' => $this->shop_data->price1,
                    'duration_3' => $this->shop_data->price3,
                    'duration_7' => $this->shop_data->price7,
                    'duration_30' => $this->shop_data->price30,
                ],
                'rental_percent_days' => [
                    'duration_1' => $this->shop_data->rental->percentage_1,
                    'duration_3' => $this->shop_data->rental->percentage_3,
                    'duration_7' => $this->shop_data->rental->percentage_7,
                    'duration_30' => $this->shop_data->rental->percentage_30,
                ],
            ]),
            'version' => $this->shop_data->version,
        ];
    }
}
