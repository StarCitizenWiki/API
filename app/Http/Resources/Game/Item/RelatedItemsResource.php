<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Models\Game\ItemData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class RelatedItemsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ItemData $itemData */
        $itemData = $this->resource;

        $pivotItem = $itemData->variantGroupItem;

        if ($pivotItem === null || ! $pivotItem->relationLoaded('variantGroup') || $pivotItem->variantGroup === null) {
            return [
                'set_name' => null,
                'base_item' => null,
                'variant_items' => [],
                'set_items' => $this->formatSetItems($itemData),
            ];
        }

        $variantGroup = $pivotItem->variantGroup;
        $groupItems = $variantGroup->items;

        $basePivot = $groupItems->firstWhere('is_base', true);

        $base = $basePivot !== null
            ? $this->formatRelatedLink($basePivot->itemData, $basePivot->variant_name ?? 'Base', true, $itemData->gameVersion->code)
            : null;

        $baseId = $basePivot?->item_data_id;

        $variants = $groupItems
            ->filter(fn ($gi): bool => $gi->item_data_id !== $itemData->id
                && $gi->item_data_id !== $baseId)
            ->map(fn ($gi): array => $this->formatRelatedLink($gi->itemData, $gi->variant_name ?? 'Base', false, $itemData->gameVersion->code))
            ->values()
            ->all();

        return [
            'set_name' => $variantGroup->set_name,
            'base_item' => $base,
            'variant_items' => $variants,
            'set_items' => $this->formatSetItems($itemData),
        ];
    }

    private function formatRelatedLink(ItemData $itemData, ?string $variantName, bool $isBase, string $versionCode): array
    {
        $manufacturer = Arr::get($itemData->data, 'stdItem.Manufacturer');

        if (is_array($manufacturer)) {
            $manufacturer = [
                'code' => $manufacturer['Code'] ?? null,
                'name' => $manufacturer['Name'] ?? null,
            ];
        } else {
            $manufacturer = null;
        }

        $uuid = $itemData->item->uuid;

        $link = [
            'uuid' => $uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'type' => $itemData->type,
            'sub_type' => $itemData->sub_type,
            'classification' => $itemData->classification,
            'is_base_variant' => $isBase,
            'manufacturer' => $this->expandManufacturerLink($manufacturer),
            'size' => $itemData->size,
            'link' => route('items.show', ['identifier' => $uuid]),
            'web_url' => route('web.items.show', ['item' => $uuid]),
            'version' => $versionCode,
        ];

        if ($variantName !== null) {
            $link['variant_name'] = $variantName;
        }

        return $link;
    }

    private function formatSetItems(ItemData $itemData): array
    {
        if (! $itemData->relationLoaded('setItems')) {
            return [];
        }

        return $itemData->setItems
            ->map(fn (ItemData $setItemData): array => [
                'uuid' => $setItemData->item->uuid,
                'name' => $setItemData->name,
                'class_name' => $setItemData->class_name,
                'type' => $setItemData->type,
                'sub_type' => $setItemData->sub_type,
                'classification' => $setItemData->classification,
                'size' => $setItemData->size,
                'link' => route('items.show', ['identifier' => $setItemData->item->uuid]),
                'web_url' => route('web.items.show', ['item' => $setItemData->item->uuid]),
            ])
            ->all();
    }

    private function expandManufacturerLink(?array $manufacturer): ?array
    {
        if ($manufacturer === null) {
            return null;
        }

        return [
            ...$manufacturer,
            'link' => route('manufacturers.show', ['manufacturer' => $manufacturer['code'] ?? 'UNKN']),
        ];
    }
}
