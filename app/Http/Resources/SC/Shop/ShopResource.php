<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Shop;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;

class ShopResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'items',
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $items = $this->whenLoaded('items');
        if (optional($this->shop_data)->exists) {
            $items = $this->items()->where('uuid', $this->shop_data->item_uuid)->get();
        }

        return [
            'uuid' => $this->uuid,
            'name_raw' => $this->name_raw,
            'name' => $this->name,
            'position' => $this->position,
            'profit_margin' => $this->profit_margin,
            'link' => $this->makeApiUrl(self::UNPACKED_SHOPS_SHOW, $this->uuid),
            'version' => $this->version,
            'items' => ShopItemResource::collection($items),
        ];
    }
}
