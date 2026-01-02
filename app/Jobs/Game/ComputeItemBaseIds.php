<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ComputeItemBaseIds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly bool $dryRun = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $classBaseCache = [];
        $tagBaseCache = [];

        ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->chunkById(250, function (Collection $items) use (&$classBaseCache, &$tagBaseCache): void {
                foreach ($items as $itemData) {
                    $baseId = $this->resolveBaseId($itemData, $classBaseCache, $tagBaseCache);

                    if ($baseId !== $itemData->base_id) {
                        if (! $this->dryRun) {
                            ItemData::query()
                                ->whereKey($itemData->id)
                                ->update(['base_id' => $baseId]);
                        }
                    }
                }
            });
    }

    /**
     * @param  array<string,?int>  $classBaseCache
     * @param  array<string,?int>  $tagBaseCache
     */
    private function resolveBaseId(ItemData $itemData, array &$classBaseCache, array &$tagBaseCache): ?int
    {
        $classBaseId = $this->resolveBaseIdFromClassName($itemData, $classBaseCache);

        if ($classBaseId !== null) {
            return $classBaseId === $itemData->id ? null : $classBaseId;
        }

        $tagBaseId = $this->resolveBaseIdFromTags($itemData, $tagBaseCache);

        return $tagBaseId === $itemData->id ? null : $tagBaseId;
    }

    /**
     * @param  array<string,?int>  $classBaseCache
     */
    private function resolveBaseIdFromClassName(ItemData $itemData, array &$classBaseCache): ?int
    {
        $className = $itemData->class_name ?? '';

        if ($className === '') {
            return null;
        }

        [$cacheKey, $baseId] = $this->lookupClassBaseId($itemData, $className, $classBaseCache);

        if ($cacheKey === null) {
            return null;
        }

        return $baseId;
    }

    /**
     * @param  array<string,?int>  $classBaseCache
     * @return array{0:?string,1:?int}
     */
    private function lookupClassBaseId(ItemData $itemData, string $className, array &$classBaseCache): array
    {
        $match = [];
        if (preg_match('/_0\d/', $className, $match, PREG_OFFSET_CAPTURE) !== 1) {
            return [null, null];
        }

        $offset = $match[0][1];
        $prefix = substr($className, 0, $offset + 3);
        if ($prefix === '') {
            return [null, null];
        }

        $typeKey = $itemData->type ?? '';
        $cacheKey = sprintf('%d|%s|%s', $itemData->game_version_id, $typeKey, $prefix);

        if (array_key_exists($cacheKey, $classBaseCache)) {
            return [$cacheKey, $classBaseCache[$cacheKey]];
        }

        $baseQuery = ItemData::query()
            ->where('game_version_id', $itemData->game_version_id);

        if ($itemData->type !== null) {
            $baseQuery->where('type', $itemData->type);
        }

        if ($itemData->type === 'Char_Armor_Backpack') {
            $baseClass = substr($className, 0, $offset);
            $name = (string) $itemData->name;

            if (str_ends_with($name, 'Backpack') && ! str_contains($name, '"Expo"')) {
                $baseClass = '<>';
            }

            if ($baseClass === '<>') {
                $classBaseCache[$cacheKey] = null;

                return [$cacheKey, null];
            }

            $baseId = $baseQuery
                ->where('class_name', 'LIKE', $baseClass.'%')
                ->orderBy('name')
                ->value('id');

            $classBaseCache[$cacheKey] = $baseId;

            return [$cacheKey, $baseId];
        }

        $baseClassChecks = [
            $prefix,
            $prefix.'_01',
            $prefix.'_01_01',
        ];

        $baseId = $baseQuery
            ->whereIn('class_name', $baseClassChecks)
            ->orderByRaw(
                'case class_name when ? then 0 when ? then 1 when ? then 2 else 3 end',
                $baseClassChecks
            )
            ->value('id');

        $classBaseCache[$cacheKey] = $baseId;

        return [$cacheKey, $baseId];
    }

    /**
     * @param  array<string,?int>  $tagBaseCache
     */
    private function resolveBaseIdFromTags(ItemData $itemData, array &$tagBaseCache): ?int
    {
        $tags = $this->extractStdItemTags($itemData);
        $groupTags = $this->resolveVariantGroupTags($tags);

        if ($groupTags === null) {
            return null;
        }

        $cacheKey = $this->buildTagGroupCacheKey($itemData, $groupTags);

        if (array_key_exists($cacheKey, $tagBaseCache)) {
            return $tagBaseCache[$cacheKey];
        }

        $query = ItemData::query()
            ->where('game_version_id', $itemData->game_version_id)
            ->whereJsonContains('data->stdItem->Tags', $groupTags['series'])
            ->whereJsonContains('data->stdItem->Tags', $groupTags['set']);

        $this->applyVariantTypeFilter($query, $itemData);

        $groupItems = $query->get();

        if ($groupItems->count() <= 1) {
            $tagBaseCache[$cacheKey] = null;

            return null;
        }

        $baseId = $this->pickBaseIdFromTagGroup($groupItems);
        $tagBaseCache[$cacheKey] = $baseId;

        return $baseId;
    }

    /**
     * @return array<int,string>
     */
    private function extractStdItemTags(ItemData $itemData): array
    {
        $tags = Arr::get($itemData->data, 'stdItem.Tags', []);

        if (! is_array($tags)) {
            $tags = [];
        }

        $rawTags = Arr::get($itemData->data, 'tags');
        if (is_string($rawTags)) {
            $tags = array_merge($tags, preg_split('/\s+/', trim($rawTags)) ?: []);
        }

        $tags = array_filter($tags, fn ($tag) => is_string($tag) && trim($tag) !== '');

        return array_values(array_unique($tags));
    }

    /**
     * @param  array<int,string>  $tags
     * @return array{series:string,set:string}|null
     */
    private function resolveVariantGroupTags(array $tags): ?array
    {
        $setTag = null;

        foreach ($tags as $tag) {
            if (preg_match('/^set_/i', $tag) === 1) {
                $setTag = $tag;
                break;
            }
        }

        if ($setTag === null) {
            return null;
        }

        $seriesTag = null;
        foreach ($tags as $tag) {
            if (preg_match('/^(set|color)_/i', $tag) === 1) {
                continue;
            }

            if ($this->isIgnoredVariantTag($tag)) {
                continue;
            }

            $seriesTag = $tag;
            break;
        }

        if ($seriesTag === null) {
            return null;
        }

        return [
            'series' => $seriesTag,
            'set' => $setTag,
        ];
    }

    private function isIgnoredVariantTag(string $tag): bool
    {
        $lower = strtolower($tag);

        if (str_starts_with($lower, 'sm_')) {
            return true;
        }

        if (str_starts_with($lower, 'texture_')) {
            return true;
        }

        if (str_contains($lower, 'armor_mobi')) {
            return true;
        }

        return in_array($lower, [
            'helmet',
            'helmetcarryable',
            'backpack',
            'flightready',
            'uneditable',
            'stocked',
            'weaponmountusable',
            'missionquestitem',
            'unifiedhead',
        ], true);
    }

    /**
     * @param  array{series:string,set:string}  $groupTags
     */
    private function buildTagGroupCacheKey(ItemData $itemData, array $groupTags): string
    {
        if ($itemData->classification !== null) {
            return sprintf(
                '%d|%s|%s|%s',
                $itemData->game_version_id,
                strtolower($groupTags['series']),
                strtolower($groupTags['set']),
                strtolower($itemData->classification)
            );
        }

        return sprintf(
            '%d|%s|%s|%s|%s',
            $itemData->game_version_id,
            strtolower($groupTags['series']),
            strtolower($groupTags['set']),
            strtolower((string) $itemData->type),
            strtolower((string) $itemData->sub_type)
        );
    }

    /**
     * @param  Collection<int,ItemData>  $groupItems
     */
    private function pickBaseIdFromTagGroup(Collection $groupItems): ?int
    {
        $sorted = $groupItems->sort(function (ItemData $left, ItemData $right): int {
            $leftColor = $this->extractColorIndex($left);
            $rightColor = $this->extractColorIndex($right);

            if ($leftColor !== null && $rightColor !== null && $leftColor !== $rightColor) {
                return $leftColor <=> $rightColor;
            }

            if ($leftColor !== null && $rightColor === null) {
                return -1;
            }

            if ($leftColor === null && $rightColor !== null) {
                return 1;
            }

            $leftClass = $left->class_name ?? '';
            $rightClass = $right->class_name ?? '';
            $classCompare = strcmp($leftClass, $rightClass);
            if ($classCompare !== 0) {
                return $classCompare;
            }

            $nameCompare = strcmp($left->name ?? '', $right->name ?? '');
            if ($nameCompare !== 0) {
                return $nameCompare;
            }

            return $left->id <=> $right->id;
        });

        return $sorted->first()?->id;
    }

    private function extractColorIndex(ItemData $itemData): ?int
    {
        $tags = $this->extractStdItemTags($itemData);

        foreach ($tags as $tag) {
            if (preg_match('/^color_(\d+)$/i', $tag, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return null;
    }

    private function applyVariantTypeFilter(Builder $query, ItemData $itemData): void
    {
        if ($itemData->classification !== null) {
            $query->where('classification', $itemData->classification);

            return;
        }

        if ($itemData->type !== null) {
            $query->where('type', $itemData->type);
        }

        if ($itemData->sub_type !== null) {
            $query->where('sub_type', $itemData->sub_type);
        }
    }
}
