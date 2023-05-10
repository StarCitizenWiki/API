<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Shop;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'shop_link_v2',
    title: 'Shop',
    properties: [
        new OA\Property(property: 'uuid', type: 'integer'),
        new OA\Property(property: 'name_raw', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'position', type: 'string'),
        new OA\Property(property: 'profit_margin', type: 'string'),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'version', type: 'string'),
        new OA\Property(property: 'item_count', type: 'integer'),
    ],
    type: 'object'
)]
class ShopLinkResource extends AbstractBaseResource
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
            'name_raw' => $this->name_raw,
            'name' => $this->name,
            'position' => $this->position,
            'profit_margin' => $this->profit_margin,
            'link' => $this->makeApiUrl(self::SHOPS_SHOW, $this->uuid),
            'version' => $this->version,
        ];
    }
}
