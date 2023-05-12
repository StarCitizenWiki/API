<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\SC\Shop\ShopResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_link_v2',
    title: 'Item Link',
    description: 'Link information to an Item',
    properties: [
        new OA\Property(property: 'uuid', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type', type: 'string'),
        new OA\Property(property: 'sub_type', type: 'string', nullable: true),
        new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link_v2'),
        new OA\Property(property: 'link', type: 'string'),

        new OA\Property(property: 'updated_at', type: 'string'),
        new OA\Property(property: 'version', type: 'string'),

    ],
    type: 'object'
)]
class ItemLinkResource extends AbstractBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'uuid' => $this->uuid ?? $this->item_uuid,
            'name' => $this->name,
            'type' => $this->type,
            'sub_type' => $this->sub_type ?? $this->item->sub_type,
            'manufacturer' => new ManufacturerLinkResource($this->manufacturer ?? $this->item->manufacturer),
            'link' => $this->makeApiUrl(self::ITEMS_SHOW, $this->uuid ?? $this->item_uuid),
            'shops' => ShopResource::collection($this->whenLoaded('shops')),

            'updated_at' => $this->updated_at,
            'version' => config('api.sc_data_version'),
        ];
    }
}
