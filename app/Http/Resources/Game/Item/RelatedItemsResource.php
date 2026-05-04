<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Item;

use App\Models\Game\ItemData;
use App\Services\ItemVariantResolver;
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
                'set_name' => $this->deriveSetNameFromSetItems($itemData),
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
            ->when(
                str_starts_with($itemData->classification ?? '', 'Ship.'),
                fn ($collection) => $collection->sortBy([
                    fn ($gameItem) => $gameItem->itemData->size ?? PHP_INT_MAX,
                    fn ($gameItem) => $gameItem->itemData->grade ?? PHP_INT_MAX,
                ]),
            )
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
            'type_label' => $itemData->type_label,
            'sub_type' => $itemData->sub_type,
            'sub_type_label' => $itemData->sub_type_label,
            'classification' => $itemData->classification,
            'classification_label' => $itemData->classification_label,
            'is_base_variant' => $isBase,
            'manufacturer' => $this->expandManufacturerLink($manufacturer),
            'size' => $itemData->size,
            'grade' => $itemData->grade,
            'grade_label' => $this->formatGradeLabel($itemData),
            'class' => $itemData->class,
            'link' => route('items.show', ['identifier' => $uuid]),
            'web_url' => route('web.items.show', ['item' => $itemData->item->slug ?? $uuid]),
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
                'type_label' => $setItemData->type_label,
                'sub_type' => $setItemData->sub_type,
                'sub_type_label' => $setItemData->sub_type_label,
                'classification' => $setItemData->classification,
                'classification_label' => $setItemData->classification_label,
                'size' => $setItemData->size,
                'link' => route('items.show', ['identifier' => $setItemData->item->uuid]),
                'web_url' => route('web.items.show', ['item' => $setItemData->item->slug ?? $setItemData->item->uuid]),
            ])
            ->all();
    }

    private function deriveSetNameFromSetItems(ItemData $itemData): ?string
    {
        if (! $itemData->relationLoaded('setItems') || $itemData->setItems->isEmpty()) {
            return null;
        }

        $names = array_values(array_filter(
            array_merge(
                [$itemData->name ?? ''],
                $itemData->setItems->map(fn (ItemData $s): string => $s->name ?? '')->all(),
            ),
            static fn (string $n): bool => $n !== '',
        ));

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

    private function formatGradeLabel(ItemData $itemData): mixed
    {
        if (! str_starts_with($itemData->classification ?? '', 'Ship.')) {
            return $itemData->grade;
        }

        return match ($itemData->grade) {
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            default => $itemData->grade,
        };
    }
}
