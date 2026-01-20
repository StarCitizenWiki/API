<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Models\Game\ItemData;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'item_link',
    title: 'Item Link',
    description: 'Link information to an Item',
    type: 'object',
    allOf: [
        new OA\Schema(
            properties: [
                new OA\Property(property: 'uuid', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'class_name', type: 'string', example: '987_jacket_03_01_04'),
                new OA\Property(property: 'type', type: 'string'),
                new OA\Property(property: 'sub_type', type: 'string', nullable: true),
                new OA\Property(property: 'classification', type: 'string', example: 'FPS.Clothing.Torso', nullable: true),
                new OA\Property(property: 'is_base_variant', type: 'boolean'),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
                new OA\Property(property: 'link', type: 'string'),
                new OA\Property(property: 'base_variant', description: 'Link to base variant item', type: 'string', nullable: true),
                new OA\Property(
                    property: 'variants',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link'),
                    nullable: true,
                ),
            ],
            type: 'object',
        ),
        new OA\Schema(ref: '#/components/schemas/metadata'),
    ]
)]
class ItemLinkResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        /** @var ItemData $itemData */
        $itemData = $this->resource;
        $item = $itemData->item;

        return [
            'uuid' => $item->uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'type' => $itemData->type,
            'sub_type' => $itemData->sub_type,
            'classification' => $itemData->classification,
            'is_base_variant' => $itemData->base_id === null,
            'manufacturer' => $itemData->relationLoaded('manufacturer')
                ? new ManufacturerLinkResource($itemData->manufacturer)
                : null,
            'link' => route('items.show', ['identifier' => $item->uuid]),
            $this->mergeWhen($itemData->base_id !== null && $itemData->relationLoaded('baseVariant'), fn () => [
                'base_variant' => route('items.show', [
                    'identifier' => $itemData->baseVariant?->item?->uuid ?? '',
                ]),
            ]),
            'variants' => self::collection($this->whenLoaded('variants')),
            'shops' => [],

            'updated_at' => $item->updated_at,
            'version' => $itemData->relationLoaded('gameVersion')
                ? $itemData->gameVersion->code
                : null,
        ];
    }
}
