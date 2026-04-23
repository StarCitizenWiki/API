<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ItemVariantResolver
{
    public const array SLOT_WORDS = ['Helmet', 'Arms', 'Legs', 'Core', 'Undersuit', 'Backpack'];

    private const array TAIL_WORDS = ['helmet', 'arms', 'legs', 'core', 'undersuit', 'backpack', 'rifle'];

    /**
     * @var array<string, array<int, ItemData>>
     */
    private array $tagGroupCache = [];

    /**
     * @var array<string, array<int, ItemData>>
     */
    private array $classNameGroupCache = [];

    /**
     * @var array<int, array<int,string>>
     */
    private array $tagsCache = [];

    public function __construct(private readonly int $gameVersionId) {}

    public function clearCaches(): void
    {
        $this->tagGroupCache = [];
        $this->classNameGroupCache = [];
        $this->tagsCache = [];
    }

    /**
     * @return array<int,string>
     */
    public function extractStdItemTags(ItemData $itemData): array
    {
        if (array_key_exists($itemData->id, $this->tagsCache)) {
            return $this->tagsCache[$itemData->id];
        }

        $tags = Arr::get($itemData->data, 'stdItem.Tags', []);

        if (! is_array($tags)) {
            $tags = [];
        }

        $rawTags = Arr::get($itemData->data, 'tags');

        if (is_string($rawTags)) {
            $tags = array_merge($tags, preg_split('/\s+/', trim($rawTags)) ?: []);
        }

        $tags = array_filter($tags, static fn ($tag) => is_string($tag) && trim($tag) !== '');

        return $this->tagsCache[$itemData->id] = array_values(array_unique($tags));
    }

    public function isIgnoredVariantTag(string $tag): bool
    {
        $lower = strtolower($tag);

        return str_starts_with($lower, 'sm_')
            || str_starts_with($lower, 'texture_')
            || str_starts_with($lower, '$')
            || str_contains($lower, 'armor_mobi')
            || in_array($lower, [
                'helmet',
                'helmetcarryable',
                'backpack',
                'flightready',
                'uneditable',
                'stocked',
                'weaponmountusable',
                'missionquestitem',
                'unifiedhead',
                'fakechestuicontainer',
                'utility',
            ], true);
    }

    public function applyVariantTypeFilter(Builder $query, ItemData $itemData): void
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
     * Find variant group by matching all non-ignored tags as a group signature.
     *
     * Collects ALL non-ignored, non-set, non-color, non-texture tags as the
     * "signature". Items sharing the same signature (and optional set tag)
     * belong to the same variant group.
     *
     * Results are cached by a key derived from the signature and type filter.
     *
     * @return array<int,ItemData>
     */
    public function findVariantGroupFromTags(ItemData $itemData): array
    {
        $tags = $this->extractStdItemTags($itemData);
        $groupSignature = $this->resolveVariantGroupSignature($tags);

        if ($groupSignature === null) {
            return [];
        }

        $cacheKey = $this->buildTagGroupCacheKey($itemData, $groupSignature);

        if (array_key_exists($cacheKey, $this->tagGroupCache)) {
            return $this->tagGroupCache[$cacheKey];
        }

        $query = ItemData::query()
            ->where('game_version_id', $this->gameVersionId);

        foreach ($groupSignature['signature'] as $sigTag) {
            $query->whereJsonContains('data->stdItem->Tags', $sigTag);
        }

        if ($groupSignature['set'] !== null) {
            $query->whereJsonContains('data->stdItem->Tags', $groupSignature['set']);
        }

        $this->applyVariantTypeFilter($query, $itemData);

        $results = $query->with(['item', 'gameVersion'])->get()->all();

        $results = $this->filterByClassNamePrefix($itemData, $results);

        $this->tagGroupCache[$cacheKey] = $results;

        return $results;
    }

    /**
     * Filter tag-grouped results to only include items sharing the same
     * class name prefix, preventing unrelated product lines from being
     * grouped together (e.g. Davlos Shirt vs Forgiveness Sweater).
     *
     * @param  array<int, ItemData>  $results
     * @return array<int, ItemData>
     */
    private function filterByClassNamePrefix(ItemData $itemData, array $results): array
    {
        $prefix = $this->extractClassNamePrefix($itemData->class_name ?? '');

        if ($prefix === null) {
            return $results;
        }

        return array_values(array_filter(
            $results,
            fn (ItemData $member): bool => $this->extractClassNamePrefix($member->class_name ?? '') === $prefix,
        ));
    }

    /**
     * Find variant group by ClassName prefix matching.
     *
     * Extracts a base prefix from the item's ClassName (stripping variant
     * suffixes) and queries for all items sharing that prefix, filtered by
     * the same type/classification.
     *
     * Results are cached by a key derived from the prefix and type filter.
     *
     * @return array<int,ItemData>
     */
    public function findVariantGroupFromClassName(ItemData $itemData): array
    {
        $className = $itemData->class_name;

        if ($className === null || $className === '') {
            return [];
        }

        $prefix = $this->extractClassNamePrefix($className);

        if ($prefix === null) {
            return [];
        }

        $cacheKey = $this->buildClassNameGroupCacheKey($itemData, $prefix);

        if (array_key_exists($cacheKey, $this->classNameGroupCache)) {
            return $this->classNameGroupCache[$cacheKey];
        }

        $query = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where(function (Builder $q) use ($prefix): void {
                $q->where('class_name', $prefix)
                    ->orWhere('class_name', 'LIKE', $prefix.'_%');
            });

        $this->applyVariantTypeFilter($query, $itemData);

        $results = $query->with(['item', 'gameVersion'])->get()->all();

        $this->classNameGroupCache[$cacheKey] = $results;

        return $results;
    }

    /**
     * Determine the base ItemData from a variant group.
     *
     * Sorts by color index (ascending), then class name, then name, then id.
     * The first item after sorting is considered the base.
     *
     * @param  array<int,ItemData>  $group
     */
    public function resolveBaseForGroup(array $group): ?ItemData
    {
        if ($group === []) {
            return null;
        }

        usort($group, function (ItemData $left, ItemData $right): int {
            $colorCmp = ($this->extractColorIndex($left) ?? PHP_INT_MAX) <=> ($this->extractColorIndex($right) ?? PHP_INT_MAX);
            if ($colorCmp !== 0) {
                return $colorCmp;
            }

            $classCmp = strcmp($left->class_name ?? '', $right->class_name ?? '');
            if ($classCmp !== 0) {
                return $classCmp;
            }

            $nameCmp = strcmp($left->name ?? '', $right->name ?? '');

            return $nameCmp !== 0 ? $nameCmp : $left->id <=> $right->id;
        });

        return $group[0];
    }

    /**
     * Resolve tags into a group signature for variant matching.
     *
     * Collects ALL non-ignored, non-set, non-color, non-texture tags as the
     * "signature". Items sharing the same signature (and optional set tag)
     * belong to the same variant group.
     *
     * Returns null when the signature is empty or consists only of tags that
     * are too broad (e.g. "pistol" matches every pistol from every manufacturer).
     *
     * @param  array<int,string>  $tags
     * @return array{signature:array<int,string>,set:?string}|null
     */
    private function resolveVariantGroupSignature(array $tags): ?array
    {
        $setTag = null;
        $signatureTags = [];

        foreach ($tags as $tag) {
            if (stripos($tag, 'set_') === 0) {
                $setTag ??= $tag;

                continue;
            }

            if (stripos($tag, 'color_') === 0) {
                continue;
            }

            if (stripos($tag, 'texture_') === 0) {
                continue;
            }

            if ($this->isIgnoredVariantTag($tag)) {
                continue;
            }
            $signatureTags[] = $tag;
        }

        if ($signatureTags === [] || $this->isTooBroadSignature($signatureTags)) {
            return null;
        }

        return [
            'signature' => $signatureTags,
            'set' => $setTag,
        ];
    }

    private function isTooBroadSignature(array $tags): bool
    {
        return count($tags) === 1
            && in_array(strtolower($tags[0]), ['pistol', 'knife', 'grenade', 'shouldered'], true);
    }

    /**
     * Extract the base ClassName prefix for variant group matching.
     *
     * Find the first `_0\d` pattern (e.g. `_01`) and use everything up to
     * and including the next segment as the prefix. This handles items with
     * multi-level version numbers like `acme_jacket_01_01_01` and avoids
     * grouping unrelated product lines like `mym_shirt_01_01_*` with
     * `mym_shirt_01_lum02_*`.
     *
     * If no `_0\d` is found, strip trailing `_SCItem` and
     * remove trailing segments that are not base version identifiers.
     * This handles items like `SHLD_GODI_S01_AllStop_SCItem`.
     */
    private function extractClassNamePrefix(string $className): ?string
    {
        if (preg_match('/_0\d/', $className, $match, PREG_OFFSET_CAPTURE) === 1) {
            $offset = $match[0][1];
            $prefix = substr($className, 0, $offset + 3);

            $rest = substr($className, $offset + 3);
            if (preg_match('/^_[a-zA-Z0-9]+/', $rest, $nextMatch)) {
                $prefix .= $nextMatch[0];
            }

            return $prefix !== '' ? $prefix : null;
        }

        $working = $className;

        if (str_ends_with(strtolower($working), '_scitem')) {
            $working = substr($working, 0, -7);
        }

        $parts = explode('_', $working);

        if (count($parts) < 2) {
            return $working;
        }

        while (count($parts) > 2) {
            $last = $parts[count($parts) - 1];

            if ($this->isBaseSegment($last)) {
                break;
            }

            array_pop($parts);
        }

        $prefix = implode('_', $parts);

        return $prefix !== '' ? $prefix : null;
    }

    private function isBaseSegment(string $segment): bool
    {
        return $segment !== ''
            && (ctype_digit($segment) || preg_match('/^S\d+$/i', $segment) === 1);
    }

    private function buildTypeFilterSuffix(ItemData $itemData): string
    {
        if ($itemData->classification !== null) {
            return strtolower($itemData->classification);
        }

        return strtolower((string) $itemData->type).'|'.strtolower((string) $itemData->sub_type);
    }

    /**
     * @param  array{signature:array<int,string>,set:?string}  $groupSignature
     */
    private function buildTagGroupCacheKey(ItemData $itemData, array $groupSignature): string
    {
        $sig = implode(',', array_map(strtolower(...), $groupSignature['signature']));
        $set = $groupSignature['set'] !== null ? strtolower($groupSignature['set']) : '';

        return sprintf('tag|%d|%s|%s|%s', $this->gameVersionId, $sig, $set, $this->buildTypeFilterSuffix($itemData));
    }

    private function buildClassNameGroupCacheKey(ItemData $itemData, string $prefix): string
    {
        return sprintf('cn|%d|%s|%s', $this->gameVersionId, $prefix, $this->buildTypeFilterSuffix($itemData));
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

    public static function computeSetNameAndVariantNames(array $names, ?array $base, array $group): array
    {
        $rawPrefix = self::longestCommonPrefix($names);

        $setName = null;
        if ($rawPrefix !== null) {
            $candidate = rtrim($rawPrefix);

            if ($candidate !== '') {
                $isWordBoundary =
                    str_ends_with($rawPrefix, ' ')
                    || in_array($candidate, $names, true)
                    || collect($names)->contains(fn (string $n): bool => str_starts_with($n, $candidate.' '));

                if (! $isWordBoundary) {
                    $lastSpace = strrpos($candidate, ' ');

                    if ($lastSpace !== false) {
                        $candidate = substr($candidate, 0, $lastSpace);
                    }
                }

                $candidate = trim($candidate);
                $setName = $candidate !== '' ? $candidate : null;
            }
        }

        if ($base !== null && $setName !== null && $setName === $base['name']) {
            $trimmed = self::trimTrailingSlotOrTypeWord($setName);

            if ($trimmed !== null) {
                $setName = $trimmed;
            }
        }

        $stripPrefix = $setName;

        if ($base !== null) {
            $baseName = $base['name'];

            $allPrefixedByBase = true;

            foreach ($names as $n) {
                if ($n === $baseName) {
                    continue;
                }

                if (! str_starts_with($n, $baseName.' ')) {
                    $allPrefixedByBase = false;
                    break;
                }
            }

            if ($allPrefixedByBase) {
                $stripPrefix = $baseName;
            }
        }

        $map = [];

        if ($base !== null) {
            $baseRemainder = $setName !== null
                ? trim(self::stripPrefix($base['name'], $setName))
                : '';
            $map[$base['uuid']] = $baseRemainder === '' ? 'Base' : $baseRemainder;
        }

        foreach ($group as $it) {
            if ($base !== null && $it['uuid'] === $base['uuid']) {
                continue;
            }

            $remainder = $stripPrefix !== null
                ? self::stripPrefix($it['name'], $stripPrefix)
                : $it['name'];

            $remainder = trim($remainder);
            $map[$it['uuid']] = $remainder === '' ? 'Base' : $remainder;
        }

        return [$setName, $map];
    }

    public static function normalizeVariantName(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $s = trim($value);
        if ($s === '' || strcasecmp($s, 'Base') === 0) {
            return 'Base';
        }

        if (preg_match('/"([^"]+)"/u', $s, $m)) {
            $q = trim($m[1]);

            return $q !== '' ? $q : null;
        }

        if (preg_match('/^[(\x{FF08}]\s*(.+?)\s*[)\x{FF09}]$/u', $s, $m)) {
            $s = trim($m[1]);
        }

        $s = preg_replace('/\s+Edition$/iu', '', $s) ?? $s;
        $s = trim($s);

        $slotAlternation = implode('|', array_map(static fn (string $w): string => preg_quote($w, '/'), self::SLOT_WORDS));

        if (preg_match('/^(?:'.$slotAlternation.')\s+(.+)$/iu', $s, $m)) {
            $s = trim($m[1]);
        }

        if (preg_match('/^(.+?)\s+(?:'.$slotAlternation.')$/iu', $s, $m)) {
            $s = trim($m[1]);
        }

        return $s !== '' ? $s : null;
    }

    public static function longestCommonPrefix(array $strings): ?string
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

    public static function stripPrefix(string $name, string $prefix): string
    {
        if ($prefix === '' || ! str_starts_with($name, $prefix)) {
            return $name;
        }

        return ltrim(Str::after($name, $prefix));
    }

    private static function trimTrailingSlotOrTypeWord(string $name): ?string
    {
        $name = trim($name);
        $parts = preg_split('/\s+/u', $name) ?: [];

        if (count($parts) < 2) {
            return null;
        }

        $last = (string) end($parts);
        $lastLower = mb_strtolower($last);

        if (! in_array($lastLower, self::TAIL_WORDS, true)) {
            return null;
        }

        array_pop($parts);
        $trimmed = trim(implode(' ', $parts));

        return $trimmed !== '' ? $trimmed : null;
    }
}
