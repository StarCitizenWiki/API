<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Support\Cache\RelatedItemsCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            array_unshift($names, $baseItem['name']);
        }

        [$setName, $variantNames] = $this->computeSetNameAndVariantNames($names, $baseItem, $groupItems);

        $base = $baseItem !== null ? $this->toBaseLink($baseItem, $setName, true) : null;

        $variants = collect($groupItems)
            ->filter(fn (array $groupItem): bool => $groupItem['uuid'] !== $item->uuid)
            ->map(function (array $groupItem) use ($variantNames): array {
                /** @var array{uuid:string,name:string} $groupItem */
                $link = $this->toBaseLink($groupItem, null, false);

                $raw = $variantNames[$groupItem['uuid']] ?? null;
                $link['variant_name'] = self::normalizeVariantName($raw);

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
     * @return array{
     *     0:?array{uuid:string,name:string},
     *     1:array<int,array{uuid:string,name:string}>
     * }
     */
    public function gatherVariantGroup(Item $item): array
    {
        $versionCode = $this->gameVersionCode;
        $itemId = $item->getAttribute('id');

        /** @var array{base_item:?array{uuid:string,name:string},group_items:array<int,array{uuid:string,name:string}>} $cachedVariantGroup */
        $cachedVariantGroup = RelatedItemsCache::rememberVariantGroup(
            $versionCode,
            $itemId,
            function () use ($item) {
                $itemData = $this->getItemDataForVersion($item);

                $versionId = $itemData->game_version_id;

                if ($itemData->base_id === null) {
                    $base = $itemData;
                    $siblings = $itemData->variants()->with(['item', 'gameVersion'])->where('game_version_id', $versionId)->get()->all();
                    $shouldFallbackToTags = $siblings === [];
                } else {
                    $base = $itemData->baseVariant()->with(['item', 'gameVersion'])->where('game_version_id', $versionId)->first();
                    $siblings = $base?->variants()->with(['item', 'gameVersion'])->where('game_version_id', $versionId)->get()->all() ?? [];
                    $shouldFallbackToTags = count($siblings) <= 1;
                }

                if ($shouldFallbackToTags) {
                    $tagGroup = $this->findVariantGroupFromTags($itemData);

                    if (count($tagGroup) > 1) {
                        return $this->toCachedVariantGroup(null, $tagGroup);
                    }

                    $classNameGroup = $this->findVariantGroupFromClassName($itemData);

                    if (count($classNameGroup) > 1) {
                        return $this->toCachedVariantGroup(null, $classNameGroup);
                    }
                }

                return $this->toCachedVariantGroup($base, $siblings);
            }
        );

        return [$cachedVariantGroup['base_item'], $cachedVariantGroup['group_items']];
    }

    /**
     * Compute set name and per-item variant names.
     * - set name: longest common prefix among names (with a base-aware trim rule)
     * - variant name: item name with the chosen prefix removed (trimmed); if empty, "Base".
     *
     * @param  array<int,string>  $names
     * @param  array{uuid:string,name:string}|null  $base
     * @param  array<int,array{uuid:string,name:string}>  $group
     * @return array{0:?string,1:array<string,string>} [setName, map(uuid=>variantName)]
     */
    public function computeSetNameAndVariantNames(array $names, ?array $base, array $group): array
    {
        // --- compute set label (what you expose as set_name) ---
        $rawPrefix = self::longestCommonPrefix($names);

        $setName = null;
        if ($rawPrefix !== null) {
            $candidate = rtrim($rawPrefix);

            if ($candidate !== '') {
                $isWordBoundary =
                    str_ends_with($rawPrefix, ' ')
                    || in_array($candidate, $names, true)
                    || collect($names)->contains(fn (string $n) => str_starts_with($n, $candidate.' '));

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

        /**
         * Base-aware refinement:
         * If the computed set name collapses to the full base item name (common when the base is the shortest string),
         * trim a trailing slot/type word so the base can expose it as variant_name.
         *
         * Examples:
         * - "Lynx Arms" -> set_name "Lynx", base variant_name "Arms"
         * - "Gemini A03 Sniper Rifle" -> set_name "Gemini A03 Sniper", base variant_name "Rifle"
         */
        if ($base !== null && $setName !== null && $setName === $base['name']) {
            $trimmed = $this->trimTrailingSlotOrTypeWord($setName);

            if ($trimmed !== null) {
                $setName = $trimmed;
            }
        }

        // --- decide what prefix to strip to get the variant remainder ---
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

            // If variants are literally "<base> <suffix>", prefer stripping the base name
            if ($allPrefixedByBase) {
                $stripPrefix = $baseName;
            }
        }

        // --- build uuid => remainder map ---
        $map = [];

        if ($base !== null) {
            $map[$base['uuid']] = 'Base';
        }

        foreach ($group as $it) {
            $remainder = $stripPrefix !== null
                ? self::stripPrefix($it['name'], $stripPrefix)
                : $it['name'];

            $remainder = trim($remainder);
            $map[$it['uuid']] = $remainder === '' ? 'Base' : $remainder;
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

        return RelatedItemsCache::rememberSetItems(
            $this->gameVersionCode,
            $className,
            function () use ($item, $className) {
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
                        ->with('item')
                        ->first();

                    if ($found !== null && $found->item->uuid !== $item->uuid) {
                        $set[] = [
                            'uuid' => $found->item->uuid,
                            'name' => $found->name,
                            'type' => $found->type,
                            'sub_type' => $found->sub_type,
                            'classification' => $found->classification,
                            'link' => $this->makeLink($found->item->uuid),
                            'web_url' => route('web.items.show', ['item' => $found->item->uuid]),
                        ];
                    }
                }

                return $set;
            }
        );
    }

    /**
     * @param  array{uuid:string,name:string,class_name?:?string,type?:?string,sub_type?:?string,classification?:?string,size?:?int,is_base_variant?:bool,manufacturer?:?array{code:?string,name:?string},version?:?string}  $item
     * @return array{uuid:string,name:string,class_name:?string,type:?string,sub_type:?string,classification:?string,is_base_variant:bool,variant_name?:?string,manufacturer:?array{code:?string,name:?string,link:string},version:?string,link:string,web_url:string,size:?int}
     */
    private function toBaseLink(array $item, ?string $setName, bool $includeVariantName): array
    {
        $link = [
            'uuid' => $item['uuid'],
            'name' => $item['name'],
            'class_name' => $item['class_name'] ?? null,
            'type' => $item['type'] ?? null,
            'sub_type' => $item['sub_type'] ?? null,
            'classification' => $item['classification'] ?? null,
            'is_base_variant' => $item['is_base_variant'] ?? true,
            'manufacturer' => $this->expandManufacturerLink($item['manufacturer'] ?? null),
            'version' => $item['version'] ?? null,
            'link' => $this->makeLink($item['uuid']),
            'web_url' => route('web.items.show', ['item' => $item['uuid']]),
            'size' => $item['size'] ?? null,
        ];

        if ($includeVariantName && $setName !== null) {
            $variant = self::stripPrefix($item['name'], $setName);
            $variant = $variant === '' ? 'Base' : $variant;
            $link['variant_name'] = self::normalizeVariantName($variant) ?? 'Base';
        }

        return $link;
    }

    /**
     * @param  array{code:?string,name:?string}|null  $manufacturer
     * @return array{code:?string,name:?string,link:string}|null
     */
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
     * If the name ends with a known slot/type word, remove that last word.
     * Returns null if no safe trim is possible.
     */
    private function trimTrailingSlotOrTypeWord(string $name): ?string
    {
        $name = trim($name);
        $parts = preg_split('/\s+/u', $name) ?: [];

        if (count($parts) < 2) {
            return null;
        }

        $last = (string) end($parts);
        $lastLower = mb_strtolower($last);

        // Extend as needed; keep it conservative to avoid over-trimming.
        $tailWords = [
            'helmet',
            'arms',
            'legs',
            'core',
            'undersuit',
            'backpack',
            'rifle',
        ];

        if (! in_array($lastLower, $tailWords, true)) {
            return null;
        }

        array_pop($parts);
        $trimmed = trim(implode(' ', $parts));

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * Find variant group by matching all non-ignored tags as a group signature.
     *
     * @return array<int,ItemData>
     */
    private function findVariantGroupFromTags(ItemData $itemData): array
    {
        $tags = $this->extractStdItemTags($itemData);
        $groupSignature = $this->resolveVariantGroupSignature($tags);

        if ($groupSignature === null) {
            return [];
        }

        $query = ItemData::query()
            ->where('game_version_id', $this->resolveGameVersion()->id);

        foreach ($groupSignature['signature'] as $sigTag) {
            $query->whereJsonContains('data->stdItem->Tags', $sigTag);
        }

        if ($groupSignature['set'] !== null) {
            $query->whereJsonContains('data->stdItem->Tags', $groupSignature['set']);
        }

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

        foreach ($tags as $tag) {
            if (stripos($tag, 'set_') === 0) {
                $setTag = $tag;
                break;
            }
        }

        $signatureTags = [];

        foreach ($tags as $tag) {
            if (stripos($tag, 'set_') === 0) {
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

        if ($signatureTags === []) {
            return null;
        }

        if ($this->isTooBroadSignature($signatureTags)) {
            return null;
        }

        return [
            'signature' => $signatureTags,
            'set' => $setTag,
        ];
    }

    /**
     * Tags that are too generic to identify a specific product line.
     * When the signature contains only these, fall through to ClassName matching.
     */
    private function isTooBroadSignature(array $tags): bool
    {
        $broadTags = [
            'pistol',
            'knife',
            'grenade',
            'shouldered',
        ];

        if (count($tags) === 1) {
            return in_array(strtolower($tags[0]), $broadTags, true);
        }

        return false;
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

        if (str_starts_with($lower, '$')) {
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
            'fakechestuicontainer',
            'utility',
        ], true);
    }

    /**
     * Find variant group by ClassName prefix matching.
     *
     * Extracts a base prefix from the item's ClassName (stripping variant
     * suffixes) and queries for all items sharing that prefix, filtered by
     * the same type/classification.
     *
     * @return array<int,ItemData>
     */
    private function findVariantGroupFromClassName(ItemData $itemData): array
    {
        $className = $itemData->class_name;

        if ($className === null || $className === '') {
            return [];
        }

        $prefix = $this->extractClassNamePrefix($className);

        if ($prefix === null) {
            return [];
        }

        $query = ItemData::query()
            ->where('game_version_id', $this->resolveGameVersion()->id)
            ->where(function (Builder $q) use ($prefix): void {
                $q->where('class_name', $prefix)
                    ->orWhere('class_name', 'LIKE', $prefix.'_%');
            });

        $this->applyVariantTypeFilter($query, $itemData);

        $results = $query->get()->all();

        return count($results) > 1 ? $results : [];
    }

    /**
     * Extract the base ClassName prefix by stripping variant suffixes.
     *
     * Strips trailing segments that do not look like base version numbers
     * (pure digits like "01" or size indicators like "S1"). Also strips
     * trailing "_SCItem".
     *
     * Examples:
     *   gmni_pistol_ballistic_01_firerats01  -> gmni_pistol_ballistic_01
     *   ksar_smg_energy_01_cc17              -> ksar_smg_energy_01
     *   utfl_melee_01_red01                  -> utfl_melee_01
     *   SHLD_GODI_S01_AllStop_SCItem         -> SHLD_GODI_S01_AllStop
     *   gmni_pistol_ballistic_01             -> gmni_pistol_ballistic_01 (unchanged)
     */
    private function extractClassNamePrefix(string $className): ?string
    {
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

    /**
     * Determine if a ClassName segment looks like a base version identifier
     * rather than a variant suffix.
     *
     * Base: "01", "02", "S1", "S3" (pure digits or size indicators)
     * Variant: "gold01", "firerats01", "arctic01", "shark", "cen01"
     */
    private function isBaseSegment(string $segment): bool
    {
        if ($segment === '') {
            return false;
        }

        if (ctype_digit($segment)) {
            return true;
        }

        if (preg_match('/^S\d+$/i', $segment) === 1) {
            return true;
        }

        return false;
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
     * @throws ModelNotFoundException
     */
    private function getItemDataForVersion(Item $item): ItemData
    {
        return ItemData::query()
            ->where('item_id', $item->getAttribute('id'))
            ->where('game_version_id', $this->resolveGameVersion()->id)
            ->with(['item', 'gameVersion'])
            ->firstOrFail();
    }

    /**
     * @param  array<int,ItemData>  $groupItems
     * @return array{
     *     base_item:?array{uuid:string,name:string},
     *     group_items:array<int,array{uuid:string,name:string}>
     * }
     */
    private function toCachedVariantGroup(?ItemData $baseItem, array $groupItems): array
    {
        return [
            'base_item' => $baseItem !== null ? $this->toCachedVariantItem($baseItem) : null,
            'group_items' => array_values(array_unique(array_map(
                fn (ItemData $groupItem): array => $this->toCachedVariantItem($groupItem),
                $groupItems
            ), SORT_REGULAR)),
        ];
    }

    /**
     * @return array{uuid:string,name:string,class_name:?string,type:?string,sub_type:?string,classification:?string,size:?int,is_base_variant:bool,manufacturer:?array{code:?string,name:?string},version:?string}
     */
    private function toCachedVariantItem(ItemData $itemData): array
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

        return [
            'uuid' => $itemData->item->uuid,
            'name' => $itemData->name,
            'class_name' => $itemData->class_name,
            'type' => $itemData->type,
            'sub_type' => $itemData->sub_type,
            'classification' => $itemData->classification,
            'size' => $itemData->size,
            'is_base_variant' => $itemData->base_id === null,
            'manufacturer' => $manufacturer,
            'version' => $itemData->gameVersion?->code,
        ];
    }

    /**
     * Extract the variant name from an item name relative to its base variant name.
     *
     * Computes the longest common prefix between the two names, strips it,
     * and normalizes the remainder.
     *
     * Examples:
     *   ('A03 "Canuto" Sniper Rifle', 'A03 Sniper Rifle') -> 'Canuto'
     *   ('ADP Arms Aqua', 'ADP Arms Black')               -> 'Aqua'
     *   ('Gallant "Executive Edition" Rifle', null)        -> null
     */
    public static function extractVariantName(string $name, ?string $baseName): ?string
    {
        if ($baseName === null) {
            return null;
        }

        $prefix = self::longestCommonPrefix([$name, $baseName]);

        if ($prefix !== null) {
            $prefix = rtrim($prefix);
        }

        $remainder = self::stripPrefix($name, $prefix ?? '');
        $remainder = trim($remainder);

        if ($remainder === '' || strcasecmp($remainder, $name) === 0) {
            return null;
        }

        return self::normalizeVariantName($remainder);
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

        // Prefer quoted: "Rust Society" -> Rust Society
        if (preg_match('/"([^"]+)"/u', $s, $m)) {
            $q = trim($m[1]);

            return $q !== '' ? $q : null;
        }

        // (Modified) or （Modified）
        if (preg_match('/^[(\x{FF08}]\s*(.+?)\s*[)\x{FF09}]$/u', $s, $m)) {
            $s = trim($m[1]);
        }

        // Hurston Edition -> Hurston
        $s = preg_replace('/\s+Edition$/iu', '', $s) ?? $s;
        $s = trim($s);

        // If this still looks like a full item name, keep only the tail after the last slot word.
        $slotWords = ['Helmet', 'Arms', 'Legs', 'Core', 'Undersuit', 'Backpack'];
        $slotAlternation = implode('|', array_map(static fn (string $w): string => preg_quote($w, '/'), $slotWords));

        // e.g. "CSP-68L Backpack Forest Camo" -> "Forest Camo"
        if (preg_match('/\b(?:'.$slotAlternation.')\b\s+(.+)$/iu', $s, $m)) {
            $candidate = trim($m[1]);
            if ($candidate !== '' && strcasecmp($candidate, $s) !== 0) {
                $s = $candidate;
            }
        }

        // Remove leading/trailing slot words if they still remain
        foreach ($slotWords as $slot) {
            if (preg_match('/^'.preg_quote($slot, '/').'\s+(.+)$/iu', $s, $m)) {
                $s = trim($m[1]);
                break;
            }
        }

        foreach ($slotWords as $slot) {
            if (preg_match('/^(.+)\s+'.preg_quote($slot, '/').'\s*$/iu', $s, $m)) {
                $s = trim($m[1]);
                break;
            }
        }

        return $s !== '' ? $s : null;
    }
}
