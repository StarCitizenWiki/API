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

    // Known special cases
    private const array SPECIAL_MERGE_PREFIXES = [
        'mrai_flightsuit_01',
        'qrt_combat_heavy_core_02',
        'qrt_combat_heavy_arms_02',
        'qrt_combat_heavy_helmet_02',
        'qrt_combat_heavy_legs_02',
        'cds_undersuit_01',
        'cds_combat_light_backpack_01',
        'cds_combat_medium_arms_04',
        'cds_combat_medium_core_04',
        'cds_combat_medium_helmet_04',
        'cds_combat_medium_legs_04',
        'srvl_combat_heavy_arms_03',
        'srvl_combat_heavy_core_03',
        'srvl_combat_heavy_helmet_03',
        'srvl_combat_heavy_legs_03',
        'eld_shirt_04',
        'eld_shirt_10',
        'cbd_hat_03',
        'cbd_shirt_01',
        'cbd_shirt_02',
        'fio_jacket_01',
        'nrs_shoes_03',
        'dmc_frontier_jacket_01',
        'dmc_frontier_gloves_01',
        'dmc_frontier_pants_01',
        'dmc_jacket_04',
        'dmc_jacket_13',
        'dmc_gloves_01',
        '987_shoes_01',
        'alb_gloves_02',
        'alb_pants_01',
        'gsb_shoes_05',
        'doom_armor_medium_helmet_02',
    ];

    private const array SHIP_VARIANT_PART_PREFIXES = [
        'ARMR',
        'HTNK',
        'QTNK',
        'RPOD',
    ];

    private const string COUNTERMEASURE_TYPE_PATTERN = '/^(Chaff|Flare|Noise|Decoy)/i';

    private const array ENTITY_TAG_SLOT_NAMES = [
        'Arms', 'Core', 'Helmet', 'Legs', 'Backpack', 'Undersuit',
        'Head', 'Feet', 'Hands', 'Shirt', 'Jacket', 'Hat',
    ];

    private const array ENTITY_TAG_RARITY_NAMES = [
        'Common', 'Uncommon', 'Rare', 'Epic',
    ];

    private const array ENTITY_TAG_WEIGHT_NAMES = [
        'Light', 'Medium', 'Heavy',
    ];

    private const array ENTITY_TAG_META_NAMES = [
        'FPS', 'Human', 'Char', 'PU',
        'CanGenerateAsLoot', 'CannotGenerateAsLoot',
        'ReceiveParentActorInteractions',
        'LootableFromSuit', 'Stackable', 'CanBeHung',
        'ActionArea', 'NPCEventTrigger', 'PlayerEventTrigger',
        'PotentiallyTrash', 'SubscriberFlair',
        'Part1', 'Part2', 'Race', 'Specialist', 'Legendary',
        'PromotionalItem', 'InGameReward', 'SpecialEventFlair',
        'TwitchDrop', 'Wikelo', 'Kaboos', 'CitCon', 'ContestedZone', 'Horizon',
        'Concierge', 'ReferralProgram', 'Unlootable',
        'Set', 'Color', 'Manufacturer',
        '1H', '2H',
        'DataCentre', 'Tutorial', 'FullBody', 'SuitArmor',
        'UnequipBlocked', 'Purpose', 'Style', 'Security',
        'PointOfInterest', 'ReservedForPlayer',
        'disableTractorBeamDetach',
        'NoneCarryableSupportingDefaultItemActions',
        'Unknown', 'Generic', 'Engineering', 'Maintenance',
        'Exterior', 'Gadget', 'Furniture', 'Tablet', 'Toy',
        'ReceiveParentActorInteractions', 'CanGenerateAsLoot',
        'ActionArea', 'Human', 'CanBeHung', 'Stackable', 'PU',
        '1H', 'Color', 'Set', 'LootableFromSuit', 'Race',
        'Manufacturer', 'CannotGenerateAsLoot',
        'NPCEventTrigger', 'PlayerEventTrigger',
        'PromotionalItem', 'FullBody', 'SubscriberFlair',
        'PotentiallyTrash', 'Epic', 'InGameReward', 'Unlootable',
        'ContestedZone', 'SpecialEventFlair',
        'Biome', 'Processing', 'Uses', 'Harmfulness', 'Type',
        'Bridge', 'Desert', 'Turret_Unmanned', 'SCItemClothing',
        'Elevator', 'Idle', 'Part1', 'Book', 'SeatAccess', 'Tools',
    ];

    private const array ENTITY_TAG_WEAPON_TYPE_NAMES = [
        'Weapon', 'Rifle', 'Pistol', 'Shotgun', 'SMG', 'Sniper',
        'LMG', 'SniperRifle', 'Laser',
        'Crosshair', 'Stocked', 'Shouldered',
        'Ballistic', 'Energy', 'Melee',
        'Railgun', 'MissileLauncher',
        'Mining',
    ];

    private const array ENTITY_TAG_ATTACHMENT_TYPE_NAMES = [
        'Attachment', 'Barrel', 'Compensator', 'Suppressor', 'Stabilizer',
        'Optic', 'Magazine', 'Holographic', 'IronSight',
    ];

    private const array ENTITY_TAG_CONSUMABLE_TYPE_NAMES = [
        'Consumable', 'Food', 'Drink', 'Medical', 'Oxygen',
        'Heal', 'Stim', 'Drug',
    ];

    private const array ENTITY_TAG_SIZE_NAMES = [
        'Size1', 'Size2', 'Size3', 'Size4', 'Size5', 'Size6', 'Size7', 'Size8', 'Size9',
        'S1', 'S2', 'S3', 'S4', 'S5', 'S6', 'S7', 'S8', 'S9',
    ];

    private const array ENTITY_TAG_LIFESTYLE_NAMES = [
        'Work', 'Outdoors', 'Rugged', 'Relax', 'EveryDay',
        'Casual', 'Industrial', 'Fashionable', 'Business',
        'Venture', 'Woodland', 'Formal',
    ];

    private const array ENTITY_TAG_GARMENT_DESCRIPTOR_NAMES = [
        'Pants', 'Coat', 'Boots', 'BootsTall', 'Pullover',
        'Shoes', 'Gloves', 'JacketLong', 'T-Shirt',
        'Harness', 'Cups', 'JumpSuit',
    ];

    private const array ENTITY_TAG_MATERIAL_NAMES = [
        'Metal', 'Mineral', 'NonMetal', 'Glass', 'Gas', 'Plasma',
    ];

    private const array ENTITY_TAG_CARGO_NAMES = [
        'Cargo', 'ProcessedGoods', 'ExternalStorage',
        '1SCU', '2SCU', '4SCU', '8SCU', '16SCU', '24SCU', '32SCU',
        'Bottle', 'Can',
    ];

    private const array ENTITY_TAG_SHIP_COMPONENT_NAMES = [
        'Seat', 'Turret', 'Door', 'DockingTube', 'Gimbal',
        'Powerplant', 'Cooler', 'QuantumDrive', 'TBO',
    ];

    private const array ENTITY_TAG_COLOR_NAMES = [
        'Grey', 'Red', 'Blue', 'White', 'Tan', 'Green',
        'Black', 'Yellow', 'DarkGrey', 'Orange', 'DarkRed',
        'Seagreen', 'Purple', 'Aqua', 'Violet',
    ];

    private const array ENTITY_TAG_MANUFACTURER_NAMES = [
        'ClarkeDefense', 'KastakArms', 'RSI', 'FlightBlade',
        'StegmansClothingAndUniforms', 'QuirinusTech', 'GreyCat',
        'DMC', 'Fiore', 'AlejoBrothers', 'EscarLimited', 'Habidash',
        'CBD', 'OpalSky', 'TrueDef', 'Caldera', 'Derion',
        'CodeBlueApparel', 'GrindstoneBoots', 'Doomsday', 'KlausWerner',
        'CoHelmsman', 'Strata', 'MacFlex', 'Behring', 'R6Pro',
        'Orbageddon', 'OdysseyII', 'NorthStar', 'Gyson',
        'Helmsman', 'Virgil', 'Lynx', 'Electron',
        '987', 'KilgoreAndPoole', 'Gemini', 'Overlord', 'Octagon',
        'Aril', 'Antium', 'CCsConversions', 'Ninetails',
        'DCDelving', 'Kaboos', 'Wikelo', 'XenoThreat', 'Volt', 'Tru',
    ];

    /** @var array<string, array<int, ItemData>> */
    private array $queryCache = [];

    /** @var array<int, array<int,string>> */
    private array $tagsCache = [];

    public function __construct(private readonly int $gameVersionId) {}

    public function isExcludedItem(ItemData $item): bool
    {
        return ! ItemRelevanceChecker::isPlayerRelevant($item->name, $item->class_name);
    }

    public function clearCaches(): void
    {
        $this->queryCache = [];
        $this->tagsCache = [];
    }

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

    /** @return array<int,ItemData> */
    public function findVariantGroupFromTags(ItemData $itemData): array
    {
        $tags = $this->extractStdItemTags($itemData);
        $groupSignature = $this->resolveVariantGroupSignature($tags);

        if ($groupSignature === null) {
            return [];
        }

        $cacheKey = $this->buildTagGroupCacheKey($itemData, $groupSignature);

        $rawResults = $this->cachedQuery($cacheKey, function () use ($itemData, $groupSignature): Builder {
            $query = ItemData::query()
                ->where('game_version_id', $this->gameVersionId);

            foreach ($groupSignature as $sigTag) {
                $query->whereJsonContains('data->stdItem->Tags', $sigTag);
            }

            $this->applyVariantTypeFilter($query, $itemData);

            return $query;
        });

        return $this->filterByClassNamePrefix($itemData, $rawResults);
    }

    public function extractPaintPrefix(ItemData $itemData): ?string
    {
        $tags = $this->extractStdItemTags($itemData);

        foreach ($tags as $tag) {
            if (stripos($tag, 'Paint_') === 0) {
                return $tag;
            }
        }

        $className = $itemData->class_name ?? '';

        if (str_starts_with(strtolower($className), 'paint_')) {
            $segments = explode('_', $className);

            if (count($segments) >= 2) {
                return 'Paint_'.$segments[1];
            }
        }

        return null;
    }

    /** @return array<int,ItemData> */
    public function findVariantGroupFromPaint(ItemData $itemData): array
    {
        $paintPrefix = $this->extractPaintPrefix($itemData);

        if ($paintPrefix === null) {
            return [];
        }

        $cacheKey = sprintf('paint|%d|%s|%s', $this->gameVersionId, $paintPrefix, strtolower((string) $itemData->classification));

        return $this->cachedQuery($cacheKey, fn (): Builder => ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('classification', $itemData->classification)
            ->whereJsonContains('data->stdItem->Tags', $paintPrefix), 2);
    }

    public function extractShipComponentPrefix(string $className): ?string
    {
        $segments = explode('_', $className);

        foreach ($segments as $i => $segment) {
            if ($i > 0 && preg_match('/^S\d+$/i', $segment)) {
                return self::nullIfEmpty(implode('_', array_slice($segments, 0, $i)));
            }
        }

        return null;
    }

    /** @return array<int,ItemData> */
    public function findShipComponentGroup(ItemData $itemData): array
    {
        $className = $itemData->class_name ?? '';

        $prefix = $this->extractShipComponentPrefix($className);

        if ($prefix === null) {
            return [];
        }

        $cacheKey = sprintf('ship_cn|%d|%s|%s', $this->gameVersionId, $prefix, $this->buildTypeFilterSuffix($itemData));
        $escapedPrefix = str_replace('_', '!_', $prefix);

        $results = $this->cachedQuery($cacheKey, function () use ($itemData, $prefix, $escapedPrefix): Builder {
            $query = ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->where(function (Builder $q) use ($prefix, $escapedPrefix): void {
                    $q->where('class_name', $prefix)
                        ->orWhereRaw("class_name LIKE ? ESCAPE '!'", [$escapedPrefix.'!_%']);
                });

            $this->applyVariantTypeFilter($query, $itemData);

            return $query;
        });

        return array_values(array_filter(
            $results,
            static function (ItemData $member) use ($prefix): bool {
                $cn = $member->class_name ?? '';

                if ($cn === $prefix) {
                    return true;
                }

                if (! str_starts_with($cn, $prefix.'_')) {
                    return false;
                }

                $remainder = substr($cn, strlen($prefix) + 1);

                return (bool) preg_match('/^S\d+$/i', $remainder);
            },
        ));
    }

    /** @return array<int,ItemData> */
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

        if ($this->isKnownFalseMergePrefix($prefix)) {
            $refined = $this->refineClassNamePrefix($className, $prefix);
            if ($refined !== null && ! $this->isKnownFalseMergePrefix($refined)) {
                $prefix = $refined;
            } else {
                return [];
            }
        }

        $cacheKey = $this->buildClassNameGroupCacheKey($itemData, $prefix);
        $escapedPrefix = str_replace('_', '!_', $prefix);

        return $this->cachedQuery($cacheKey, function () use ($itemData, $prefix, $escapedPrefix): Builder {
            $query = ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->where(function (Builder $q) use ($prefix, $escapedPrefix): void {
                    $q->where('class_name', $prefix)
                        ->orWhereRaw("class_name LIKE ? ESCAPE '!'", [$escapedPrefix.'!_%']);
                });

            $this->applyVariantTypeFilter($query, $itemData);

            return $query;
        });
    }

    /** @param array<int,ItemData> $group */
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

    public function extractClassNamePrefix(string $className): ?string
    {
        $countermeasurePrefix = $this->extractCountermeasurePrefix($className);
        if ($countermeasurePrefix !== null) {
            return $countermeasurePrefix;
        }

        $segments = explode('_', $className);

        if ($segments === []) {
            return null;
        }

        $firstSegment = $segments[0];
        $isUppercase = strtoupper($firstSegment) === $firstSegment && preg_match('/[A-Z]/', $firstSegment);

        if ($isUppercase && count($segments) > 3 && preg_match('/^S\d+$/i', $segments[2]) && str_ends_with($className, '_SCItem')) {
            return self::nullIfEmpty(strtoupper($segments[0].'_'.$segments[1].'_'.$segments[2]));
        }

        $shipVariantPrefix = $this->extractShipVariantPartPrefix($className, $segments, $isUppercase);

        if ($shipVariantPrefix !== null) {
            return $shipVariantPrefix;
        }

        if ($isUppercase && $firstSegment === 'MRCK' && count($segments) >= 4 && preg_match('/^S\d+$/i', $segments[1])) {
            return self::nullIfEmpty(implode('_', array_slice($segments, 0, 4)));
        }

        if ($isUppercase) {
            foreach ($segments as $i => $segment) {
                if (preg_match('/^S\d+$/i', $segment)) {
                    if ($i >= 2 && $segments[0] !== 'MRCK') {
                        return self::nullIfEmpty(implode('_', array_slice($segments, 0, 2)));
                    }

                    return self::nullIfEmpty(implode('_', array_slice($segments, 0, $i + 1)));
                }
            }

            return null;
        }

        $foundNonNumeric = false;
        $anchorIndex = null;

        foreach ($segments as $i => $segment) {
            if (preg_match('/^\d+$/', $segment)) {
                if ($foundNonNumeric && $anchorIndex === null) {
                    $anchorIndex = $i;
                }
            } else {
                $foundNonNumeric = true;
            }
        }

        if ($anchorIndex === null) {
            foreach ($segments as $i => $segment) {
                if (preg_match('/^s\d+$/i', $segment)) {
                    return self::nullIfEmpty(implode('_', array_slice($segments, 0, $i + 1)));
                }
            }

            return null;
        }

        $prefix = implode('_', array_slice($segments, 0, $anchorIndex + 1));

        for ($i = $anchorIndex + 1, $iMax = count($segments); $i < $iMax; $i++) {
            $segment = $segments[$i];

            if (preg_match('/^\d+$/', $segment)) {
                continue;
            }

            if (preg_match('/^s\d+$/i', $segment)) {
                $prefix .= '_'.strtolower($segment);

                return self::nullIfEmpty($prefix);
            }

            if (preg_match('/^x\d+$/i', $segment)) {
                $prefix .= '_'.strtolower($segment);

                continue;
            }

            break;
        }

        return $prefix !== '' ? $prefix : null;
    }

    /** @return array<int,string> */
    public function extractEntityTagNames(ItemData $itemData): array
    {
        $data = $itemData->data;

        if ($data === null) {
            return [];
        }

        $tagMap = $data['entity_tag_map'] ?? null;

        if (! is_array($tagMap)) {
            return [];
        }

        $excludeNames = self::entityTagExcludeNames();

        $names = [];

        foreach ($tagMap as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $name = $entry['name'] ?? null;

            if ($name === null || $name === '') {
                continue;
            }

            if (! in_array(strtolower($name), $excludeNames, true)) {
                $names[] = $name;
            }
        }

        return $names;
    }

    public function resolveSetNameFromEntityTags(array $group): ?string
    {
        if ($group === []) {
            return null;
        }

        $allSets = [];

        foreach ($group as $item) {
            $names = $this->extractEntityTagNames($item);

            if ($names === []) {
                return null;
            }

            $allSets[] = array_unique($names);
        }

        $common = $allSets[0];

        for ($i = 1, $iMax = count($allSets); $i < $iMax; $i++) {
            $common = array_values(array_intersect($common, $allSets[$i]));
        }

        return $common !== [] ? $common[0] : null;
    }

    /** @return array{0: string|null, 1: array<string,string>} */
    public static function computeSetNameAndVariantNames(array $names, ?array $base, array $group): array
    {
        $setName = self::deriveSetNameFromNames($names);

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

        self::stripCommonSuffix($map);

        return [$setName, $map];
    }

    public static function deriveSetNameFromNames(array $names): ?string
    {
        $rawPrefix = self::longestCommonPrefix($names);

        if ($rawPrefix === null) {
            return null;
        }

        $candidate = rtrim($rawPrefix);

        if ($candidate === '') {
            return null;
        }

        $isWordBoundary = str_ends_with($rawPrefix, ' ')
            || in_array($candidate, $names, true)
            || collect($names)->every(fn (string $n): bool => str_starts_with($n, $candidate.' ') || str_starts_with($n, $candidate.'-') || $n === $candidate);

        if (! $isWordBoundary) {
            $lastSpace = strrpos($candidate, ' ');

            if ($lastSpace !== false) {
                $candidate = substr($candidate, 0, $lastSpace);
            } else {
                return null;
            }
        }

        $candidate = trim($candidate);

        if ($candidate !== '' && mb_strlen($candidate) < 3 && ! in_array($candidate, $names, true)) {
            return null;
        }

        return $candidate !== '' ? $candidate : null;
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

    private static function stripCommonSuffix(array &$map): void
    {
        $values = array_filter($map, fn (string $v): bool => $v !== 'Base' && $v !== '');

        if (count($values) < 2) {
            return;
        }

        $suffix = self::longestCommonSuffix(array_values($values));

        if ($suffix === null || ! str_starts_with($suffix, ' ') || mb_strlen(trim($suffix)) === 0) {
            return;
        }

        foreach ($map as $uuid => $name) {
            if ($name === 'Base') {
                continue;
            }

            if (str_ends_with($name, $suffix)) {
                $stripped = trim(substr($name, 0, -mb_strlen($suffix)));
                $map[$uuid] = $stripped === '' ? 'Base' : $stripped;
            }
        }
    }

    private static function longestCommonSuffix(array $strings): ?string
    {
        $reversed = array_map(static fn (string $s): string => strrev($s), $strings);

        return (($lcp = self::longestCommonPrefix($reversed)) !== null) ? strrev($lcp) : null;
    }

    private function resolveVariantGroupSignature(array $tags): ?array
    {
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

        if ($signatureTags === [] || $this->isTooBroadSignature($signatureTags)) {
            return null;
        }

        return $signatureTags;
    }

    private function isTooBroadSignature(array $tags): bool
    {
        return count($tags) === 1
            && in_array(strtolower($tags[0]), ['pistol', 'knife', 'grenade', 'shouldered'], true);
    }

    private function filterByClassNamePrefix(ItemData $itemData, array $results): array
    {
        $prefix = $this->extractClassNamePrefix($itemData->class_name ?? '');

        if ($prefix === null) {
            $prefix = $this->resolveFallbackPrefix($itemData->class_name ?? '');
        }

        if ($prefix === null) {
            return $results;
        }

        if ($this->isKnownFalseMergePrefix($prefix)) {
            $refined = $this->refineClassNamePrefix($itemData->class_name ?? '', $prefix);

            if ($refined !== null && ! $this->isKnownFalseMergePrefix($refined)) {
                return array_values(array_filter(
                    $results,
                    static fn (ItemData $member): bool => ($member->class_name ?? '') === $refined
                        || str_starts_with((string) $member->class_name, $refined.'_'),
                ));
            }

            return [];
        }

        return array_values(array_filter(
            $results,
            function (ItemData $member) use ($prefix): bool {
                $memberPrefix = $this->extractClassNamePrefix($member->class_name ?? '');
                if ($memberPrefix !== null) {
                    return $memberPrefix === $prefix;
                }

                return $this->resolveFallbackPrefix($member->class_name ?? '') === $prefix;
            },
        ));
    }

    private function buildTagGroupCacheKey(ItemData $itemData, array $groupSignature): string
    {
        $sig = implode(',', array_map(strtolower(...), $groupSignature));

        return sprintf('tag|%d|%s|%s', $this->gameVersionId, $sig, $this->buildTypeFilterSuffix($itemData));
    }

    private function buildTypeFilterSuffix(ItemData $itemData): string
    {
        if ($itemData->classification !== null) {
            return strtolower($itemData->classification);
        }

        return strtolower((string) $itemData->type).'|'.strtolower((string) $itemData->sub_type);
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

    public function isKnownFalseMergePrefix(string $prefix): bool
    {
        return in_array($prefix, self::SPECIAL_MERGE_PREFIXES, true);
    }

    private function extractCountermeasurePrefix(string $className): ?string
    {
        if (! str_contains($className, '_CML_')) {
            return null;
        }

        $cmlPos = strpos($className, '_CML_');
        $prefix = substr($className, 0, $cmlPos + 4);
        $afterCml = substr($className, $cmlPos + 5);

        return preg_match(self::COUNTERMEASURE_TYPE_PATTERN, $afterCml)
            ? self::nullIfEmpty($prefix)
            : null;
    }

    private function extractShipVariantPartPrefix(string $className, array $segments, bool $isUppercase): ?string
    {
        if (! $isUppercase || count($segments) < 3) {
            return null;
        }

        if (! in_array($segments[0], self::SHIP_VARIANT_PART_PREFIXES, true)) {
            return null;
        }

        return self::nullIfEmpty($segments[0].'_'.$segments[1].'_'.$segments[2]);
    }

    private static function nullIfEmpty(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private function resolveFallbackPrefix(string $className): ?string
    {
        $segments = explode('_', $className);

        return count($segments) > 2
            ? implode('_', array_slice($segments, 0, -1))
            : null;
    }

    public function refineClassNamePrefix(string $className, string $currentPrefix): ?string
    {
        if (! str_starts_with($className, $currentPrefix.'_')) {
            return null;
        }

        $afterPrefix = substr($className, strlen($currentPrefix) + 1);

        if ($afterPrefix === '') {
            return null;
        }

        $segments = explode('_', $afterPrefix);

        if ($segments === [] || $segments[0] === '') {
            return null;
        }

        $nextSegment = $segments[0];

        if (preg_match('/^\d+$/', $nextSegment)) {
            if (count($segments) > 1) {
                return $currentPrefix.'_'.$nextSegment;
            }

            return null;
        }

        return $currentPrefix.'_'.$nextSegment;
    }

    /** @return array<string> */
    private static function entityTagExcludeNames(): array
    {
        static $names;

        return $names ??= array_map('strtolower', array_merge(
            self::ENTITY_TAG_SLOT_NAMES,
            self::ENTITY_TAG_RARITY_NAMES,
            self::ENTITY_TAG_WEIGHT_NAMES,
            self::ENTITY_TAG_META_NAMES,
            self::ENTITY_TAG_WEAPON_TYPE_NAMES,
            self::ENTITY_TAG_ATTACHMENT_TYPE_NAMES,
            self::ENTITY_TAG_CONSUMABLE_TYPE_NAMES,
            self::ENTITY_TAG_SIZE_NAMES,
            self::ENTITY_TAG_LIFESTYLE_NAMES,
            self::ENTITY_TAG_GARMENT_DESCRIPTOR_NAMES,
            self::ENTITY_TAG_MATERIAL_NAMES,
            self::ENTITY_TAG_CARGO_NAMES,
            self::ENTITY_TAG_SHIP_COMPONENT_NAMES,
            self::ENTITY_TAG_COLOR_NAMES,
            self::ENTITY_TAG_MANUFACTURER_NAMES,
        ));
    }

    /** @return array<int, ItemData> */
    private function cachedQuery(string $cacheKey, callable $queryBuilder, int $minCount = 1): array
    {
        if (array_key_exists($cacheKey, $this->queryCache)) {
            return $this->queryCache[$cacheKey];
        }

        $results = $queryBuilder()->with(['item', 'gameVersion'])->get()->all();

        if (count($results) < $minCount) {
            $results = [];
        }

        return $this->queryCache[$cacheKey] = $results;
    }
}
