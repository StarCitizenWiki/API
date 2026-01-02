<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class RelatedItemsBuilder
{
    private ?GameVersion $resolvedGameVersion = null;

    public function __construct(private readonly ?string $gameVersionCode = null) {}

    /**
     * Build the related_items payload for a given Item.
     *
     * @return array{set_name:?string,base_item:?array,variant_items:array<int,array>,set_items:array<int,array>}
     */
    public function build(Item $item): array
    {
        [$baseItem, $groupItems] = $this->gatherVariantGroup($item);

        $names = collect($groupItems)->pluck('name')->all();
        if ($baseItem !== null) {
            array_unshift($names, $baseItem->name);
        }

        [$setName, $variantNames] = $this->computeSetNameAndVariantNames($names, $baseItem, $groupItems);

        $base = $baseItem !== null ? $this->toBaseLink($baseItem, $setName, true) : null;
        $variants = collect($groupItems)
            ->filter(fn (ItemData $i) => $i->item->uuid !== $item->uuid) // exclude current item
            ->map(function (ItemData $it) use ($variantNames) {
                $link = $this->toBaseLink($it, null, false);
                $fullVariantName = $variantNames[$it->item->uuid] ?? null;
                $link['variant_name'] = $fullVariantName !== null
                    ? $this->extractVariantSuffix($fullVariantName)
                    : null;

                return $link;
            })
            ->values()
            ->all();

        $setItems = $this->findSetItems($item);

        return [
            'set_name' => $setName,
            'base_item' => $base,
            'variant_items' => $variants,
            'set_items' => $setItems,
        ];
    }

    /**
     * Determine base item and full variant group for an item.
     *
     * @return array{0:?ItemData,1:array<int,ItemData>}
     */
    public function gatherVariantGroup(Item $item): array
    {
        $itemData = $this->getItemDataForVersion($item);

        if ($itemData->base_id === null) {
            $base = $itemData;
            $siblings = $itemData->variants()->get()->all();
            $shouldFallbackToTags = $siblings === [];
        } else {
            $base = $itemData->baseVariant()->first();
            $siblings = $base?->variants()->get()->all() ?? [];
            $shouldFallbackToTags = count($siblings) <= 1;
        }

        if ($shouldFallbackToTags) {
            $tagGroup = $this->findVariantGroupFromTags($itemData);
            if (count($tagGroup) > 1) {
                return [null, $tagGroup];
            }
        }

        return [$base, $siblings];
    }

    /**
     * Compute set name and per-item variant names.
     * - set name: longest common prefix among names
     * - variant name: item name with the set name prefix removed (trimmed); if empty, "Base".
     *
     * @param  array<int,string>  $names
     * @param  array<int,ItemData>  $group
     * @return array{0:?string,1:array<string,string>} [setName, map(uuid=>variantName)]
     */
    public function computeSetNameAndVariantNames(array $names, ?ItemData $base, array $group): array
    {
        $rawPrefix = $this->longestCommonPrefix($names);
        if ($rawPrefix !== null) {
            $endsWithSpace = str_ends_with($rawPrefix, ' ');
            $setName = rtrim($rawPrefix);
            if (! $endsWithSpace) {
                $lastSpace = strrpos($setName, ' ');
                if ($lastSpace !== false) {
                    $setName = substr($setName, 0, $lastSpace);
                }
            }
            $setName = $setName === '' ? null : $setName;
        } else {
            $setName = null;
        }

        $map = [];
        if ($base !== null) {
            $map[$base->item->uuid] = 'Base';
        }

        foreach ($group as $it) {
            $variant = $this->stripPrefix($it->name, (string) $setName);
            $map[$it->item->uuid] = $variant === '' ? 'Base' : $variant;
        }

        return [$setName, $map];
    }

    /**
     * Find set items for armor/clothing by replacing the part token in class_name.
     *
     * @return array<int,array{uuid:string,name:string,type:?string,sub_type:?string,link:string}>
     */
    public function findSetItems(Item $item): array
    {
        $itemData = $this->getItemDataForVersion($item);
        $className = $itemData->class_name ?? '';
        if ($className === '') {
            return [];
        }

        $parts = config('item_sets.parts', ['helmet', 'core', 'arms', 'legs']);
        $currentPart = null;
        foreach ($parts as $part) {
            if (str_contains($className, '_'.$part.'_')) {
                $currentPart = $part;
                break;
            }
        }
        if ($currentPart === null) {
            return [];
        }

        $set = [];
        foreach ($parts as $part) {
            if ($part === $currentPart) {
                continue;
            }
            $candidate = $this->replaceFirst('_'.$currentPart.'_', '_'.$part.'_', $className);
            $found = ItemData::query()
                ->where('class_name', $candidate)
                ->where('game_version_id', $this->resolveGameVersion()->id)
                ->first();
            if ($found !== null && $found->item->uuid !== $item->uuid) {
                $set[] = [
                    'uuid' => $found->item->uuid,
                    'name' => $found->name,
                    'type' => $found->type,
                    'sub_type' => $found->sub_type,
                    'link' => $this->makeLink($found->item->uuid),
                ];
            }
        }

        return $set;
    }

    private function toBaseLink(ItemData $it, ?string $setName, bool $includeVariantName): array
    {
        $link = [
            'uuid' => $it->item->uuid,
            'name' => $it->name,
            'link' => $this->makeLink($it->item->uuid),
        ];
        if ($includeVariantName && $setName !== null) {
            $variant = $this->stripPrefix($it->name, $setName);
            $link['variant_name'] = $variant === '' ? 'Base' : $variant;
        }

        return $link;
    }

    private function makeLink(string $uuid): string
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return $baseUrl.'/api/items/'.$uuid;
    }

    private function replaceFirst(string $search, string $replace, string $subject): string
    {
        $pos = strpos($subject, $search);
        if ($pos === false) {
            return $subject;
        }

        return substr($subject, 0, $pos).$replace.substr($subject, $pos + strlen($search));
    }

    private function longestCommonPrefix(array $strings): ?string
    {
        if (count($strings) === 0) {
            return null;
        }
        $prefix = $strings[0];
        foreach ($strings as $s) {
            $i = 0;
            $max = min(strlen($prefix), strlen($s));
            while ($i < $max && $prefix[$i] === $s[$i]) {
                $i++;
            }
            $prefix = substr($prefix, 0, $i);
            if ($prefix === '') {
                return null;
            }
        }

        return $prefix;
    }

    private function stripPrefix(string $name, string $prefix): string
    {
        if ($prefix === '') {
            return $name;
        }
        if (str_starts_with($name, $prefix)) {
            $rest = substr($name, strlen($prefix));

            return ltrim($rest);
        }

        return $name;
    }

    /**
     * @return array<int,ItemData>
     */
    private function findVariantGroupFromTags(ItemData $itemData): array
    {
        $tags = $this->extractStdItemTags($itemData);
        $groupTags = $this->resolveVariantGroupTags($tags);

        if ($groupTags === null) {
            return [];
        }

        $query = ItemData::query()
            ->where('game_version_id', $this->resolveGameVersion()->id)
            ->whereJsonContains('data->stdItem->Tags', $groupTags['series'])
            ->whereJsonContains('data->stdItem->Tags', $groupTags['set']);

        $this->applyVariantTypeFilter($query, $itemData);

        return $query->get()->all();
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
            if (stripos($tag, 'set_') === 0) {
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

    /**
     * Resolve the game version to use for queries.
     * Caches the result to avoid multiple database queries.
     */
    private function resolveGameVersion(): GameVersion
    {
        if ($this->resolvedGameVersion === null) {
            $this->resolvedGameVersion = GameVersion::resolveRequestedOrDefault($this->gameVersionCode);
        }

        return $this->resolvedGameVersion;
    }

    /**
     * Get the ItemData for the current game version from an Item.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function getItemDataForVersion(Item $item): ItemData
    {
        return $item->data()
            ->whereHas('gameVersion', fn ($q) => $q->where('id', $this->resolveGameVersion()->id))
            ->firstOrFail();
    }

    /**
     * Extract the color/variant suffix by removing the part/type name.
     * Handles quoted variant names (e.g., "Red Alert") and regular variants.
     */
    private function extractVariantSuffix(string $variantName): string
    {
        $trimmed = trim($variantName);
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('/"([^"]+)"/', $trimmed, $matches)) {
            return $matches[1];
        }

        $words = preg_split('/\s+/', $trimmed);
        array_shift($words);

        return implode(' ', $words);
    }
}
