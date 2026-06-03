<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\ItemData;
use App\Services\ItemVariantResolver;
use Illuminate\Http\Request;

class RelatedItemsResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        /** @var ItemData $itemData */
        $itemData = $this->resource;

        $pivotItem = $itemData->variantGroupItem;

        if ($pivotItem === null || ! $pivotItem->relationLoaded('variantGroup') || $pivotItem->variantGroup === null) {
            return [
                'set_name' => $this->deriveSetNameFromSetItems($itemData),
                'base_item' => null,
                'variant_items' => [],
                'set_items' => $this->formatSetItems($itemData, $request),
            ];
        }

        $variantGroup = $pivotItem->variantGroup;
        $groupItems = $variantGroup->items;

        $basePivot = $groupItems->firstWhere('is_base', true);

        $base = $basePivot !== null
            ? $this->formatRelatedLink($basePivot->itemData, $basePivot->variant_name ?? 'Base', true, $itemData->gameVersion->code, $request)
            : null;

        $baseId = $basePivot?->item_data_id;

        $variants = $groupItems
            ->filter(fn ($gi): bool => $gi->item_data_id !== $itemData->id
                && $gi->item_data_id !== $baseId)
            ->sortBy([
                ['itemData.size', 'asc'],
                ['itemData.grade', 'asc'],
            ])
            ->map(fn ($gi): array => $this->formatRelatedLink($gi->itemData, $gi->variant_name ?? 'Base', false, $itemData->gameVersion->code, $request))
            ->values()
            ->all();

        return [
            'set_name' => $variantGroup->set_name,
            'base_item' => $base,
            'variant_items' => $variants,
            'set_items' => $this->formatSetItems($itemData, $request),
        ];
    }

    private function formatRelatedLink(ItemData $itemData, ?string $variantName, bool $isBase, string $versionCode, Request $request): array
    {
        $uuid = $itemData->item->uuid;

        $link = [
            'uuid' => $uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'type' => $this->stripItemTypePrefix($itemData->type),
            'type_label' => $itemData->type_label,
            'sub_type' => $itemData->sub_type,
            'sub_type_label' => $itemData->sub_type_label,
            'classification' => $itemData->classification,
            'classification_label' => $itemData->classification_label,
            'is_base_variant' => $isBase,
            'manufacturer' => $this->formatManufacturerLink($itemData),
            'size' => $itemData->size,
            'grade' => $itemData->grade,
            'grade_label' => ItemData::formatGrade($itemData->grade, $itemData->classification),
            'class' => $itemData->class,
            'link' => $this->urlWithVersion(route('items.show', ['identifier' => $uuid]), $request),
            'web_url' => $this->urlWithVersion(route('web.items.show', ['item' => $itemData->item->slug ?? $uuid]), $request),
            'version' => $versionCode,
        ];

        if ($variantName !== null) {
            $link['variant_name'] = $variantName;
        }

        return $link;
    }

    private function formatSetItems(ItemData $itemData, Request $request): array
    {
        if (! $itemData->relationLoaded('setItems')) {
            return [];
        }

        return $itemData->setItems
            ->map(fn (ItemData $setItemData): array => [
                'uuid' => $setItemData->item->uuid,
                'name' => $setItemData->name,
                'class_name' => $setItemData->class_name,
                'type' => $this->stripItemTypePrefix($setItemData->type),
                'type_label' => $setItemData->type_label,
                'sub_type' => $setItemData->sub_type,
                'sub_type_label' => $setItemData->sub_type_label,
                'classification' => $setItemData->classification,
                'classification_label' => $setItemData->classification_label,
                'size' => $setItemData->size,
                'link' => $this->urlWithVersion(route('items.show', ['identifier' => $setItemData->item->uuid]), $request),
                'web_url' => $this->urlWithVersion(route('web.items.show', ['item' => $setItemData->item->slug ?? $setItemData->item->uuid]), $request),
            ])
            ->all();
    }

    private function deriveSetNameFromSetItems(ItemData $itemData): ?string
    {
        if (! $itemData->relationLoaded('setItems') || $itemData->setItems->isEmpty()) {
            return null;
        }

        $names = array_merge(
            [$itemData->name ?? ''],
            $itemData->setItems->map(fn (ItemData $s): string => $s->name ?? '')->all(),
        )
                |> (static fn ($x) => array_filter($x, static fn (string $n): bool => $n !== ''))
                |> array_values(...);

        if (count($names) < 2) {
            return null;
        }

        $slotPattern = '/\s+('.implode('|', array_map(
            static fn (string $w): string => preg_quote($w, '/'),
            ItemVariantResolver::SLOT_WORDS,
        )).')\s+/iu';

        $strippedNames = array_map(
            static fn (string $name): string => trim(preg_replace($slotPattern, ' ', $name) ?? $name),
            $names,
        );

        return ItemVariantResolver::deriveSetNameFromNames($strippedNames);
    }

    private function formatManufacturerLink(ItemData $itemData): ?array
    {
        if (! $itemData->relationLoaded('manufacturer') || $itemData->manufacturer === null) {
            return null;
        }

        return [
            'code' => $itemData->manufacturer->code,
            'name' => $itemData->manufacturer->name,
            'link' => route('manufacturers.show', ['manufacturer' => $itemData->manufacturer->code ?: 'UNKN']),
        ];
    }
}
