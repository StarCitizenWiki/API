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
                new OA\Property(property: 'uuid', description: 'Unique identifier of the item.', type: 'string'),
                new OA\Property(property: 'name', description: 'Display name of the item.', type: 'string'),
                new OA\Property(property: 'class_name', description: 'Internal class name of the item definition.', type: 'string', example: '987_jacket_03_01_04'),
                new OA\Property(property: 'type', description: 'Item type identifier (e.g. Weapon, Armor, Clothing).', type: 'string'),
                new OA\Property(property: 'type_label', description: 'Human-readable label for the item type.', type: 'string', nullable: true),
                new OA\Property(property: 'sub_type', description: 'Item sub-type identifier.', type: 'string', nullable: true),
                new OA\Property(property: 'sub_type_label', description: 'Human-readable label for the item sub-type.', type: 'string', nullable: true),
                new OA\Property(property: 'classification', description: 'Dot-separated classification path (e.g. FPS.Clothing.Torso).', type: 'string', example: 'FPS.Clothing.Torso', nullable: true),
                new OA\Property(property: 'classification_label', description: 'Human-readable label for the item classification.', type: 'string', nullable: true),
                new OA\Property(property: 'is_base_variant', description: 'Whether this item is the base variant.', type: 'boolean'),
                new OA\Property(property: 'variant_name', description: 'Extracted variant name, e.g. "Executive Edition" or "Aqua".', type: 'string', nullable: true),
                new OA\Property(property: 'manufacturer', ref: '#/components/schemas/manufacturer_link'),
                new OA\Property(property: 'link', description: 'API URL for the full item resource.', type: 'string'),
                new OA\Property(property: 'web_url', description: 'Web URL for the item detail page.', type: 'string', nullable: true),
                new OA\Property(property: 'size', description: 'Item size (1-12 for vehicle items, smaller for FPS).', type: 'integer', nullable: true),
                new OA\Property(property: 'base_variant', description: 'API URL of the base variant item. Only present for variant items.', type: 'string', nullable: true),
                new OA\Property(
                    property: 'variants',
                    description: 'List of variant items sharing the same base item.',
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
            'type' => $this->stripItemTypePrefix($itemData->type),
            'type_label' => $itemData->type_label,
            'sub_type' => $itemData->sub_type,
            'sub_type_label' => $itemData->sub_type_label,
            'classification' => $itemData->classification,
            'classification_label' => $itemData->classification_label,
            'is_base_variant' => $itemData->base_id === null,
            'variant_name' => $itemData->relationLoaded('variantGroupItem') && $itemData->variantGroupItem !== null
                ? $itemData->variantGroupItem->variant_name
                : null,
            'manufacturer' => $itemData->relationLoaded('manufacturer')
                ? new ManufacturerLinkResource($itemData->manufacturer)
                : null,
            'link' => $this->urlWithVersion(route('items.show', ['identifier' => $item->uuid]), $request),
            'web_url' => $this->urlWithVersion(route('web.items.show', ['item' => $item->slug ?? $item->uuid]), $request),
            'size' => $itemData->size,
            $this->mergeWhen($itemData->base_id !== null && $itemData->relationLoaded('baseVariant'), fn () => [
                'base_variant' => route('items.show', [
                    'identifier' => $itemData->baseVariant?->item?->uuid ?? '',
                ]),
            ]),
            'variants' => self::collection($this->whenLoaded('variants')),

            'updated_at' => $item->updated_at,
            'version' => $itemData->relationLoaded('gameVersion')
                ? $itemData->gameVersion->code
                : null,
        ];
    }
}
