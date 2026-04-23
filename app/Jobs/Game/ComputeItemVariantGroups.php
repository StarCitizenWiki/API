<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use App\Models\Game\VariantGroup;
use App\Models\Game\VariantGroupItem;
use App\Services\ItemVariantResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ComputeItemVariantGroups implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
    ) {}

    public function handle(): void
    {
        $resolver = new ItemVariantResolver($this->gameVersionId);

        $this->computeVariantGroups($resolver);
    }

    private function computeVariantGroups(ItemVariantResolver $resolver): void
    {
        VariantGroup::query()
            ->where('game_version_id', $this->gameVersionId)
            ->delete();

        $processedIds = [];

        ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->with(['item', 'gameVersion'])
            ->chunkById(250, function (Collection $items) use ($resolver, &$processedIds): void {
                foreach ($items as $itemData) {
                    if (isset($processedIds[$itemData->id])) {
                        continue;
                    }

                    $group = array_values(array_filter(
                        $this->resolveGroup($resolver, $itemData),
                        fn (ItemData $member): bool => ! isset($processedIds[$member->id]),
                    ));

                    if (count($group) < 2) {
                        $this->updateBaseId($itemData, null);

                        continue;
                    }

                    $base = $this->resolveExistingBase($group) ?? $resolver->resolveBaseForGroup($group);

                    foreach ($group as $member) {
                        $processedIds[$member->id] = true;
                    }

                    $this->persistGroup($group, $base);
                }

                $resolver->clearCaches();
            });
    }

    /**
     * If any items in the group already have base_id set, find the existing base.
     * The base is the item whose ID is referenced by others' base_id, or the
     * item with base_id = null if it's the only one.
     *
     * @param  array<int, ItemData>  $group
     */
    private function resolveExistingBase(array $group): ?ItemData
    {
        $byId = collect($group)->keyBy('id');
        $baseIdCounts = [];
        $nullBaseIdMember = null;

        foreach ($group as $member) {
            if ($member->base_id !== null && $byId->has($member->base_id)) {
                $baseIdCounts[$member->base_id] = ($baseIdCounts[$member->base_id] ?? 0) + 1;
            } elseif ($member->base_id === null && $nullBaseIdMember === null) {
                $nullBaseIdMember = $member;
            }
        }

        if ($baseIdCounts !== []) {
            arsort($baseIdCounts);

            return $byId[array_key_first($baseIdCounts)];
        }

        return $nullBaseIdMember;
    }

    private function resolveGroup(ItemVariantResolver $resolver, ItemData $itemData): array
    {
        if ($itemData->base_id !== null) {
            $base = ItemData::query()
                ->where('id', $itemData->base_id)
                ->where('game_version_id', $this->gameVersionId)
                ->with(['item', 'gameVersion'])
                ->first();

            if ($base !== null) {
                $siblings = $base->variants()
                    ->with(['item', 'gameVersion'])
                    ->where('game_version_id', $this->gameVersionId)
                    ->get()
                    ->all();

                if (count($siblings) >= 1) {
                    return array_merge([$base], $siblings);
                }
            }
        }

        $tagGroup = $resolver->findVariantGroupFromTags($itemData);

        if (count($tagGroup) > 1) {
            return $tagGroup;
        }

        $classNameGroup = $resolver->findVariantGroupFromClassName($itemData);

        if (count($classNameGroup) > 1) {
            return $classNameGroup;
        }

        return [$itemData];
    }

    private function persistGroup(array $group, ItemData $base): void
    {
        $names = array_map(fn (ItemData $item): string => $item->name ?? '', $group);

        [$setName, $variantNames] = ItemVariantResolver::computeSetNameAndVariantNames(
            $names,
            ['uuid' => $base->item->uuid ?? '', 'name' => $base->name ?? ''],
            array_map(fn (ItemData $item): array => ['uuid' => $item->item->uuid ?? '', 'name' => $item->name ?? ''], $group)
        );

        $variantGroup = VariantGroup::query()->create([
            'game_version_id' => $this->gameVersionId,
            'set_name' => $setName,
        ]);

        $sortOrder = 0;

        foreach ($group as $itemData) {
            $isBase = $itemData->id === $base->id;

            VariantGroupItem::query()->create([
                'variant_group_id' => $variantGroup->id,
                'item_data_id' => $itemData->id,
                'variant_name' => ItemVariantResolver::normalizeVariantName($variantNames[$itemData->item->uuid ?? ''] ?? null),
                'sort_order' => $sortOrder++,
                'is_base' => $isBase,
            ]);

            $this->updateBaseId($itemData, $isBase ? null : $base->id);
        }
    }

    private function updateBaseId(ItemData $itemData, ?int $baseId): void
    {
        if ($baseId !== $itemData->base_id) {
            ItemData::query()
                ->whereKey($itemData->id)
                ->update(['base_id' => $baseId]);
        }
    }
}
