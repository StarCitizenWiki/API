<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Shop;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\Item\ItemResource;
use Illuminate\Http\Request;

class ShopResource extends AbstractTranslationResource
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
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name_raw' => $this->name_raw,
            'name' => $this->name,
            'position' => $this->position,
            'profit_margin' => $this->profit_margin,
            'version' => $this->version,
            'items' => ShopItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
