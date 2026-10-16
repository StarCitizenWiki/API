<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * !! WARNING !!
 * Here be dragons. I couldn't bring myself to manually find rules for item variant grouping.
 *
 * This code is a best-effort attempt to match CIG's grouping, by LLM.
 * Best to just leave it be.
 */

/**
 * Derives variant groups for one game version's items.
 *
 * resolveGroup() routes each item through CLASSIFICATION_ROUTES, else the
 * first matching PATTERN_ROUTES row; the matched route owns the item and
 * its handler returns the members, folding to the ungrouped singleton.
 * All member lists are in (class_name, id) order. Below the public API
 * the private logic is grouped by route domain: routing, ship, armor,
 * weapons, stems, tag/class-name, taxonomy, labels, shared plumbing.
 */
class ItemVariantResolver
{
    public const array SLOT_WORDS = ['Helmet', 'Arms', 'Legs', 'Core', 'Undersuit', 'Backpack'];

    /** Armor lane classification -> the slot word its members carry. */
    public const array ARMOR_LANE_SLOT = [
        'FPS.Armor.Helmet' => 'Helmet',
        'FPS.Armor.Arms' => 'Arms',
        'FPS.Armor.Legs' => 'Legs',
        'FPS.Armor.Torso' => 'Core',
        'FPS.Armor.Undersuit' => 'Undersuit',
        'FPS.Armor.Backpack' => 'Backpack',
    ];

    /** @var array<string, string> classification -> handler method */
    private const array CLASSIFICATION_ROUTES = [
        'Ship.Cooler' => 'findShipLineGroup',
        'Ship.PowerPlant.Power' => 'findShipLineGroup',
        'Ship.Shield' => 'findShipLineGroup',
        'Ship.QuantumDrive' => 'findShipLineGroup',
        'Ship.Radar.MidRangeRadar' => 'findShipLineGroup',
        'Ship.LifeSupportGenerator' => 'findShipLineGroup',
        'Ship.EMP' => 'findShipLineGroup',
        'Ship.FuelIntake.Fuel' => 'findShipLineGroup',
        'Ship.Weapon.Rocket' => 'findShipLineGroup',
        'Ship.MissileLauncher.MissileRack' => 'findShipLineGroup',
        'Ship.Weapon.Gun' => 'findShipLineGroup',
        'Ship.FlightController' => 'findShipLineGroup',
        'Ship.Mining.Gun' => 'findShipLineGroup',
        'Mining.Module' => 'findShipLineGroup',

        'Ship.Paints' => 'findVariantGroupFromPaint',
    ];

    /**
     * @var array<int, array{0: string, 1: string, 2: string}> [kind, pattern, handler]
     */
    private const array PATTERN_ROUTES = [
        ['classification-prefix', 'Ship.Missile.', 'findShipLineGroup'],
        ['classification-prefix', 'Ship.', 'noGroup'],
        ['classification-prefix', 'FPS.Armor.', 'findArmorLineGroup'],
        ['classification-prefix', 'FPS.Weapon', 'findWeaponDomainGroup'],
        ['type-prefix', 'Char_', 'findVariantGroupFromStem'],
        ['any', '', 'findTagOrClassNameGroup'],
    ];

    private const array WEAPON_KIND_LEXICON = [
        'Sniper Rifle',
        'Assault Rifle',
        'Twin Shotgun',
        'Frag Pistol',
        'Toy Pistol',
        'Combat Knife',
        'Frag Grenade',
        'Grenade Launcher',
        'Rocket Launcher',
        'Missile Launcher',
        'Multi-Tool',
        'Medical Device',
        'Monocular Rangefinder',
        'Tractor Beam',
        'Fire Extinguisher',
        'Laser Pointer',
        'Light Stick',
        'Tractor Beam Attachment',
        'Medical Attachment',
        'Mining Attachment',
        'SRT Attachment',
        'Cutter Attachment',
        'Rifle',
        'Shotgun',
        'SMG',
        'LMG',
        'Pistol',
        'Knife',
        'Blade',
        'Railgun',
        'SRT',
        'Flashlight',
        'Rescue Light',
        'Compensator1',
        'Compensator2',
        'Compensator3',
        'Stabilizer1',
        'Stabilizer2',
        'Stabilizer3',
        'Suppressor1',
        'Suppressor2',
        'Suppressor3',
        'Flash Hider1',
        'Flash Hider2',
        'Flash Hider3',
    ];

    /** @var array<string, array<int, ItemData>> */
    private array $queryCache = [];

    /** @var array<string, array{0: array<string, array<int, ItemData>>, 1: array<string, string>, 2: array<int, string>}> */
    private array $lineBucketCache = [];

    /** @var array<int, array<int,string>> */
    private array $tagsCache = [];

    private ?SetTagTaxonomy $taxonomy;

    // item id -> its >=2-member weapon component; computed once per domain because the name/family composition is domain-wide.
    /** @var array<int, array<int, ItemData>>|null */
    private ?array $weaponComponents = null;

    public function __construct(
        private readonly int $gameVersionId,
        ?SetTagTaxonomy $setTagTaxonomy = null,
    ) {
        $this->taxonomy = $setTagTaxonomy;
    }

    /**
     * The item's variant group; [$item] alone means "ungrouped".
     *
     * @return array<int, ItemData>
     */
    public function resolveGroup(ItemData $itemData): array
    {
        $handler = self::CLASSIFICATION_ROUTES[(string) $itemData->classification]
            ?? $this->patternRouteHandler($itemData);

        $group = $this->{$handler}($itemData);

        return count($group) > 1 ? $group : [$itemData];
    }

    public function clearCaches(): void
    {
        $this->queryCache = [];
        $this->tagsCache = [];
    }

    /** @param array<int, ItemData> $group */
    public function resolveBaseForGroup(array $group): ?ItemData
    {
        if ($group === []) {
            return null;
        }

        usort($group, fn (ItemData $l, ItemData $r): int => [
            $this->extractColorIndex($l) ?? PHP_INT_MAX, $l->class_name ?? '', $l->name ?? '', $l->id,
        ] <=> [
            $this->extractColorIndex($r) ?? PHP_INT_MAX, $r->class_name ?? '', $r->name ?? '', $r->id,
        ]);

        return $group[0];
    }

    public function resolveArmorLine(ItemData $itemData): ?string
    {
        $line = self::armorLine($itemData->classification, $itemData->name);

        if ($line === null) {
            return null;
        }

        [, $display, $assign] = $this->armorLaneBuckets((string) $itemData->classification);

        return $display[$assign[$itemData->id] ?? ''] ?? $line;
    }

    public function resolveSetNameFromEntityTags(array $group): ?string
    {
        if ($group === []) {
            return null;
        }

        $setTagUuid = $this->setTagTaxonomy()->setRowUuid();

        if ($setTagUuid === null) {
            return null;
        }

        $candidatesPerItem = [];

        foreach ($group as $item) {
            $candidatesPerItem[] = $this->setTagTaxonomy()->setTagCandidates($item, $setTagUuid);
        }

        // CIG leaves stray members untagged (a bare Set row where siblings carry the named set)
        // , so the tag must cover two thirds of the group, not every member.
        $total = count($candidatesPerItem);
        $coverage = [];
        foreach ($candidatesPerItem as $candidates) {
            foreach ($candidates as $uuid => $tagName) {
                $coverage[$uuid]['name'] = $tagName;
                $coverage[$uuid]['count'] = ($coverage[$uuid]['count'] ?? 0) + 1;
            }
        }

        $common = array_filter($coverage, static fn (array $entry): bool => $entry['count'] >= max(min(2, $total), $total * 2 / 3));

        foreach ($common as $entry) {
            $needle = mb_strtolower($entry['name']);

            if (array_any($group, fn (ItemData $item): bool => $item->name !== null && str_contains(mb_strtolower($item->name), $needle))) {
                return $entry['name'];
            }
        }

        return null;
    }

    /**
     * Canonical line families across all armor lanes, keyed by line.
     *
     * @return array{0: array<string, array<int, ItemData>>, 1: array<string, string>}
     *                                                                                 line key => members (all lanes), line key => display line
     */
    public function armorLineFamilies(): array
    {
        $buckets = [];
        $display = [];

        foreach (array_keys(self::ARMOR_LANE_SLOT) as $classification) {
            [$laneBuckets, $laneDisplay] = $this->armorLaneBuckets($classification);

            foreach ($laneBuckets as $key => $members) {
                $buckets[$key] = array_merge($buckets[$key] ?? [], $members);
            }

            $display += $laneDisplay;
        }

        return [$buckets, $display];
    }

    /** The armor product line: the words before the lane's slot word. */
    public static function armorLine(?string $classification, ?string $name): ?string
    {
        $slot = self::ARMOR_LANE_SLOT[(string) $classification] ?? null;

        if ($slot === null || $name === null) {
            return null;
        }

        $normalized = str_replace("\u{00A0}", ' ', $name);

        if (preg_match('/^(.+?)\s+'.preg_quote($slot, '/').'(\s|$)/iu', $normalized, $m) !== 1) {
            return null;
        }

        $line = trim($m[1]);

        return $line === '' ? null : $line;
    }

    /**
     * The colorway after the line and slot words ("Venture Arms Tan" ->
     * "Tan"); with the canonical line also the inverted shape ("Neoni
     * Tamarack Helmet" with line "Neoni" -> "Tamarack"). Callers whose
     * armor line is null skip the call: without it the slot word never
     * matched, so there is no colorway to extract.
     */
    public static function armorColorway(?string $classification, ?string $name, string $line): ?string
    {
        $slot = self::ARMOR_LANE_SLOT[(string) $classification] ?? null;

        if ($slot === null || $name === null) {
            return null;
        }

        $normalized = str_replace("\u{00A0}", ' ', $name);

        $pattern = '/^'.preg_quote($line, '/').'\s+(.*?)\s*'.preg_quote($slot, '/').'(?:\s+(.+))?$/iu';

        if (preg_match($pattern, $normalized, $m) !== 1) {
            return null;
        }

        return implode(' ', array_filter([trim($m[2] ?? ''), trim($m[1])], static fn (string $part): bool => $part !== ''));
    }

    /**
     * Ship line key: (identity tokens, model stem), from the class name.
     * Size segments, bare numerals/pod counts, seeker codes and mark numbers are axes;
     * all-one-case letter segments are maker/family codes (JSPN, TALN, misl);
     * every other segment builds the model stem.
     * Mount-position suffixes are per-mount factory spellings and stay in the stem,
     * so mirrored installs do not group.
     * A trailing size marker strips from the stem only with display-name corroboration.
     * NULL = tweak spelling, debug placeholder or no identity tokens at all.
     *
     * @return array{0: string, 1: string}|null
     */
    public static function shipLineKey(?string $className, ?string $name): ?array
    {
        if ($className === null || $className === '') {
            return null;
        }

        // Functional-tweak flags of one hull installation: variant-specific
        // spellings, never separately acquirable products.
        if (preg_match('/_(ResistGasclouds|SM_TE|Piercing|Pirate|Idris)(_|$)/', $className) === 1) {
            return null;
        }

        // Grammar words are packaging tokens carrying no product identity;
        // stable because they are part of CIG's class-name grammar, not
        // product spellings.
        $segments = explode('_', $className)
                |> (fn ($x) => array_filter($x, static fn (string $segment): bool => $segment !== '' && ! in_array($segment, ['SCItem', 'Flight', 'Blade', 'HND', 'SPD'], true)))
                |> array_values(...);

        $identity = [];
        $model = [];

        foreach ($segments as $i => $segment) {
            // Size segments are axis data (the size column owns the value).
            if ($i > 0 && preg_match('/^S\d+$/i', $segment) === 1) {
                continue;
            }

            // Bare numerals and pod-count spellings are size/count axes.
            if (preg_match('/^\d+x?$/', $segment) === 1) {
                continue;
            }

            // Seeker codes are warhead axes of one missile line; {CS, EM, IR}
            // is the complete enum in the data and nothing else in the ship
            // lane carries these segments.
            if (in_array(strtoupper($segment), ['CS', 'EM', 'IR'], true)) {
                continue;
            }

            // Mark numbers ('MK1'..'MK3') are edition ladders.
            if (preg_match('/^MK\d+$/', $segment) === 1) {
                continue;
            }

            // All-one-case letter segments are maker/family codes; mixed-case
            // segments are model words.
            if (preg_match('/^[A-Za-z]{2,6}$/', $segment) === 1
                && (strtolower($segment) === $segment || strtoupper($segment) === $segment)) {
                $identity[] = strtoupper($segment);

                continue;
            }

            $model[] = $segment;
        }

        $modelToken = implode('', $model);

        if ($modelToken === '' && $identity === []) {
            return null;
        }

        if (strcasecmp($modelToken, 'Placeholder') === 0) {
            return null;
        }

        return [implode('|', $identity), strtolower(self::stripSizeMarker($modelToken, $model, $name))];
    }

    /**
     * Cross-lane armor spelling: the display name carries a foreign slot
     * word and not the lane's own. CIG shipped "ADP-mk4 Core Justified"
     * classed as both Helmet and Legs; the classification is the bug, so
     * the name is the signal.
     */
    public static function isCrossLaneArmorSpelling(?string $classification, ?string $name): bool
    {
        $laneSlot = self::ARMOR_LANE_SLOT[(string) $classification] ?? null;

        if ($laneSlot === null || $name === null || $name === '') {
            return false;
        }

        $foreign = false;

        foreach (self::SLOT_WORDS as $slot) {
            if ($slot !== $laneSlot && self::nameCarriesSlotWord($name, $slot)) {
                $foreign = true;
                break;
            }
        }

        return $foreign && ! self::nameCarriesSlotWord($name, $laneSlot);
    }

    /**
     * @param  array<int, array{name: string, uuid: string, classification?: string}>  $group
     * @param  array{name: string, uuid: string, classification?: string}|null  $base
     * @return array{0: string|null, 1: array<string, string>}
     */
    public static function computeSetNameAndVariantNames(array $names, ?array $base, array $group, ?string $tagSetName = null): array
    {
        // Tag-anchored pre-strip: names carrying the set-tag prefix shed it
        // before prefix derivation, so pools with mixed name shapes (stale
        // model names, sibling models sharing one set) still label cleanly.
        $preStrippedUuids = [];
        $anchor = $tagSetName !== null ? $tagSetName.' ' : null;
        $originalNames = array_column($group, 'name', 'uuid');

        if ($anchor !== null) {
            $strippedNames = [];
            $strippedGroup = [];

            foreach ($group as $it) {
                if (str_starts_with($it['name'], $anchor)) {
                    $preStrippedUuids[$it['uuid']] = true;
                    $it['name'] = mb_substr($it['name'], strlen($anchor));
                }

                $strippedGroup[] = $it;
                $strippedNames[] = $it['name'];
            }

            if ($preStrippedUuids !== []) {
                $names = $strippedNames;
                $group = $strippedGroup;

                if ($base !== null && isset($preStrippedUuids[$base['uuid']])) {
                    $base['name'] = mb_substr($base['name'], strlen($anchor));
                }
            } else {
                $anchor = null;
            }
        }

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

            if (array_all($names, fn (string $n): bool => $n === $baseName || str_starts_with($n, $baseName.' '))) {
                $stripPrefix = $baseName;
            }
        }

        $map = [];
        $ownLabeled = [];

        if ($base !== null) {
            $own = self::memberOwnColorway([
                'name' => $originalNames[$base['uuid']] ?? $base['name'],
                'classification' => $base['classification'] ?? null,
            ]);
            $baseRemainder = $own ?? ($setName !== null
                ? trim(self::stripPrefix($base['name'], $setName))
                : '');
            $map[$base['uuid']] = $baseRemainder === '' ? 'Base' : $baseRemainder;
        }

        foreach ($group as $it) {
            if ($base !== null && $it['uuid'] === $base['uuid']) {
                continue;
            }

            // Own labels parse the ORIGINAL name: the anchor pre-strip
            // consumes the line and breaks the name grammar.
            $own = self::memberOwnColorway([
                'name' => $originalNames[$it['uuid']] ?? $it['name'],
                'classification' => $it['classification'] ?? null,
            ]);

            if ($own !== null) {
                $map[$it['uuid']] = $own;
                $ownLabeled[$it['uuid']] = true;

                continue;
            }

            $remainder = $stripPrefix !== null
                ? self::stripPrefix($it['name'], $stripPrefix)
                : $it['name'];

            $remainder = trim($remainder);
            // "Base" belongs to the base pivot alone. A non-base member
            // with an empty remainder is a duplicate bare name (a second
            // "TrueDef-Pro Arms"); it keeps its full name, quote-wrapped
            // so normalizeVariantName cannot strip the slot word off it.
            $map[$it['uuid']] = $remainder === ''
                ? '"'.$originalNames[$it['uuid']].'"'
                : $remainder;
        }

        self::stripCommonSuffix($map);

        if ($anchor !== null) {
            self::stripMajorityLeadingWord($map, $base, $preStrippedUuids, $originalNames, $ownLabeled);
        }

        // A name twin of the base (two "ADP-mk4 Helmet Woodland" items)
        // produces the same label; the base pivot yields to "Base" so the
        // pair stays distinguishable.
        if ($base !== null) {
            $baseLabel = $map[$base['uuid']] ?? null;

            if ($baseLabel !== null && $baseLabel !== 'Base'
                && in_array($baseLabel, array_diff_key($map, [$base['uuid'] => null]), true)) {
                $map[$base['uuid']] = 'Base';
            }
        }

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
            || str_ends_with($rawPrefix, '-')
            || in_array($candidate, $names, true)
            || collect($names)->every(fn (string $n): bool => str_starts_with($n, $candidate.' ') || str_starts_with($n, $candidate.'-') || $n === $candidate);

        if (! $isWordBoundary) {
            $lastBoundary = max(
                strrpos($candidate, ' ') ?: 0,
                strrpos($candidate, '-') ?: 0,
            );

            if ($lastBoundary > 0) {
                $candidate = substr($candidate, 0, $lastBoundary);
            } else {
                return null;
            }
        }

        $candidate = trim($candidate, ' -');

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

        if (preg_match('/^\(\s*(.+?)\s*\)$/u', $s, $m)) {
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

        $remainder = Str::after($name, $prefix);

        if (str_starts_with($remainder, ' ') || str_starts_with($remainder, '-')) {
            $remainder = substr($remainder, 1);
        }

        return trim($remainder);
    }

    public function extractClassNamePrefix(string $className): ?string
    {
        $segments = explode('_', $className);

        if ($segments === []) {
            return null;
        }

        $firstSegment = $segments[0];
        $isUppercase = strtoupper($firstSegment) === $firstSegment && preg_match('/[A-Z]/', $firstSegment);

        if ($isUppercase && count($segments) > 3 && preg_match('/^S\d+$/i', $segments[2]) && str_ends_with($className, '_SCItem')) {
            return self::nullIfEmpty(strtoupper($segments[0].'_'.$segments[1].'_'.$segments[2]));
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

    private function patternRouteHandler(ItemData $itemData): string
    {
        foreach (self::PATTERN_ROUTES as [$kind, $pattern, $handler]) {
            if ($kind === 'any'
                || ($kind === 'classification-prefix' && str_starts_with((string) $itemData->classification, $pattern))
                || ($kind === 'type-prefix' && str_starts_with((string) $itemData->type, $pattern))) {
                return $handler;
            }
        }

        // The any row terminates the table; passing the loop means the
        // table was edited into a state that silently drops items.
        throw new \LogicException('PATTERN_ROUTES must end with an any route.');
    }

    /** The class-name group wins when it is a strict superset of a smaller tag group. */
    private function findTagOrClassNameGroup(ItemData $itemData): array
    {
        $tagGroup = $this->findVariantGroupFromTags($itemData);
        $classNameGroup = $this->findVariantGroupFromClassName($itemData);

        $classNameIds = array_flip(array_map(static fn (ItemData $itemData): int => $itemData->id, $classNameGroup));

        if (count($tagGroup) > 1
            && count($classNameGroup) > count($tagGroup)
            && array_all($tagGroup, fn (ItemData $needle): bool => isset($classNameIds[$needle->id]))) {
            return $classNameGroup;
        }

        $group = count($tagGroup) > 1 ? $tagGroup : $classNameGroup;

        return count($group) > 1 ? $group : [];
    }

    /** Fixed hull structure: no variant groups, ever. */
    private function noGroup(): array
    {
        return [];
    }

    /**
     * Ship line group: same-classification peers whose line key matches.
     * Duplicate class names stay distinct members: the map keys on id.
     *
     * @return array<int, ItemData>
     */
    private function findShipLineGroup(ItemData $itemData): array
    {
        $key = self::shipLineKey($itemData->class_name, $itemData->name);

        if ($key === null) {
            return [];
        }

        $classification = (string) $itemData->classification;

        $peers = $this->cachedQuery(
            sprintf('ship-line|%d|%s', $this->gameVersionId, $classification),
            fn (): Builder => ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->where('classification', $classification),
        );

        $members = [];

        foreach ($peers as $peer) {
            if (self::shipLineKey($peer->class_name, $peer->name) === $key) {
                $members[$peer->id] = $peer;
            }
        }

        return array_values($members);
    }

    /**
     * A size marker strips only with display-name corroboration; one-word
     * names never corroborate.
     *
     * @param  array<int, string>  $modelSegments
     */
    private static function stripSizeMarker(string $modelToken, array $modelSegments, ?string $name): string
    {
        if ($name === null || $name === '') {
            return $modelToken;
        }

        // CIG separates some tokens with U+00A0/U+202F only; \s misses them.
        $normalized = str_replace(["\u{00A0}", "\u{202F}"], ' ', $name);
        $trimmed = trim($normalized);
        $words = preg_split('/\s+/u', $trimmed) ?: [];
        $prefixWords = '';
        $edition = null;

        // Space-attached edition words are rule-derivable: short alphabetic
        // trailing words whose prefix is the remaining stem ('QuadraCell MT',
        // 'Cryo-Star EX'). Digits never mark editions (SNS-R5x/R6x are size
        // steps); 'Fierell Cascade' keeps its product word (too long).
        if (count($words) >= 2) {
            $lastWord = $words[count($words) - 1];

            if (preg_match('/^[a-z]{2,4}$/i', $lastWord) === 1) {
                $edition = $lastWord;
                $prefixWords = implode(' ', array_slice($words, 0, -1));
            }
        }

        // Hyphen-attached editions are textually identical to hyphenated
        // product words ('BroadSpec-Go' vs 'Cryo-Star'); only a list
        // separates them.
        if ($edition === null) {
            $hyphenParts = preg_split('/-+/u', $trimmed) ?: [];
            $lastPart = $hyphenParts[count($hyphenParts) - 1] ?? '';

            if (in_array(strtolower($lastPart), ['go', 'lite', 'max'], true)) {
                $edition = $lastPart;
                $prefixWords = implode('-', array_slice($hyphenParts, 0, -1));
            }
        }

        if (
            $edition !== null
            && str_ends_with(strtolower($modelToken), strtolower($edition))
        ) {
            $candidate = substr($modelToken, 0, -strlen($edition));
            $namePrefix = str_replace([' ', '-'], '', $prefixWords);

            if ($candidate !== '' && strcasecmp($candidate, $namePrefix) === 0) {
                return $candidate;
            }
        }

        // The name forgot its edition word: the trailing class segment goes,
        // the name equals the remaining stem ('BroadSpec_Lite' -> 'BroadSpec').
        $tailSegment = $modelSegments[count($modelSegments) - 1] ?? null;

        if (
            $tailSegment !== null && count($modelSegments) >= 2
            && str_ends_with(strtolower($modelToken), strtolower($tailSegment))
        ) {
            $candidate = substr($modelToken, 0, -strlen($tailSegment));

            if ($candidate !== '' && strcasecmp(trim($normalized), $candidate) === 0) {
                return $candidate;
            }
        }

        return $modelToken;
    }

    /** @return array<int,ItemData> */
    private function findVariantGroupFromPaint(ItemData $itemData): array
    {
        $paintPrefix = $this->extractPaintPrefix($itemData);

        if ($paintPrefix !== null) {
            $cacheKey = sprintf('paint|%d|%s|%s', $this->gameVersionId, $paintPrefix, strtolower((string) $itemData->classification));

            $tagGroup = $this->cachedQuery($cacheKey, fn (): Builder => ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->where('classification', $itemData->classification)
                ->whereJsonContains('data->stdItem->Tags', $paintPrefix), 2);

            if (count($tagGroup) > 1) {
                return $tagGroup;
            }
        }

        return $this->findVariantGroupFromClassName($itemData);
    }

    /**
     * Canonical line families per lane. The exact words before the slot
     * word are the line, but CIG also emits an inverted shape ("Neoni
     * Tamarack Helmet") where the colorway precedes the slot word and
     * splits the line. Singleton lines relax by dropping trailing words
     * until they join an existing family or run out of words, so inverted
     * families collapse onto their shared line while true singletons
     * ("Bokto") stay alone.
     *
     * @return array{0: array<string, array<int, ItemData>>, 1: array<string, string>, 2: array<int, string>}
     *                                                                                                        line key => members, line key => display line, item id => line key
     */
    private function armorLaneBuckets(string $classification): array
    {
        $cacheKey = sprintf('armorlane|%d|%s', $this->gameVersionId, $classification);

        if (array_key_exists($cacheKey, $this->lineBucketCache)) {
            return $this->lineBucketCache[$cacheKey];
        }

        // Bucket the whole lane by line once; LIKE prefix matching cannot
        // express line equality (NBSP names, embedded underscores). Shadow
        // SKUs (nodraw_*, is_player_relevant = false) stay out of families
        // so they never surface as set partners.
        $laneItems = $this->cachedQuery($cacheKey.'|q', fn (): Builder => ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('is_player_relevant', true)
            ->where('classification', $classification));

        $entries = [];

        foreach ($laneItems as $member) {
            $line = self::armorLine($member->classification, $member->name);

            if ($line !== null) {
                $entries[] = ['item' => $member, 'words' => preg_split('/\s+/', $line), 'display' => $line];
            }
        }

        // Exact buckets with peers anchor their line; only then do
        // singleton lines relax toward an anchored family (longest first).
        $exactCounts = array_count_values(array_map(
            static fn (array $entry): string => mb_strtolower($entry['display']),
            $entries
        ));

        usort($entries, static fn (array $a, array $b): int => strlen(implode(' ', $b['words'])) <=> strlen(implode(' ', $a['words'])));

        $buckets = [];
        $display = [];
        $assign = [];

        foreach ($entries as $entry) {
            $words = $entry['words'];

            while (true) {
                $key = mb_strtolower(implode(' ', $words));

                if (($exactCounts[$key] ?? 0) >= 2 || isset($buckets[$key])) {
                    $buckets[$key][] = $entry['item'];
                    // words is the split of display, so the first count($words)
                    // of it IS the bucket line re-split.
                    $display[$key] ??= implode(' ', array_slice($entry['words'], 0, count($words)));
                    $assign[$entry['item']->id] = $key;

                    break;
                }

                // A line that is a prefix of an anchored family ("Zeus
                // Exploration" next to "Zeus Exploration Suit") joins that
                // family rather than falling past it to a shorter line.
                $prefixTarget = null;

                foreach ($buckets as $bucketKey => $_) {
                    if (str_starts_with($bucketKey, $key.' ')) {
                        $prefixTarget = $bucketKey;

                        break;
                    }
                }

                if ($prefixTarget !== null) {
                    $buckets[$prefixTarget][] = $entry['item'];
                    $assign[$entry['item']->id] = $prefixTarget;

                    break;
                }

                if (count($words) === 1) {
                    $buckets[$key][] = $entry['item'];
                    $display[$key] ??= implode(' ', $words);
                    $assign[$entry['item']->id] = $key;

                    break;
                }

                array_pop($words);
            }
        }

        foreach ($laneItems as $member) {
            if (isset($assign[$member->id])) {
                continue;
            }

            $normalized = mb_strtolower(str_replace("\u{00A0}", ' ', (string) $member->name));
            $bestKey = null;
            $bestLength = 0;

            foreach ($display as $key => $line) {
                $lineLower = mb_strtolower($line);

                if (($normalized === $lineLower || str_starts_with($normalized, $lineLower.' '))
                    && mb_strlen($lineLower) > $bestLength) {
                    $bestKey = $key;
                    $bestLength = mb_strlen($lineLower);
                }
            }

            if ($bestKey !== null) {
                $buckets[$bestKey][] = $member;
                $assign[$member->id] = $bestKey;
            }
        }

        return $this->lineBucketCache[$cacheKey] = [$buckets, $display, $assign];
    }

    /**
     * Armor lane membership by name line: same lane, same leading words
     * before the slot word -- one group per product line. Unparseable
     * names fall back to the tag or class-name pool, restricted to
     * equally unparseable peers so line-grouped items never ride a
     * fallback pool; cross-lane spellings stay ungrouped singletons.
     *
     * @return array<int, ItemData>
     */
    private function findArmorLineGroup(ItemData $itemData): array
    {
        $line = self::armorLine($itemData->classification, $itemData->name);

        if ($line === null) {
            if (self::isCrossLaneArmorSpelling($itemData->classification, $itemData->name)) {
                return [];
            }

            [$buckets, , $assign] = $this->armorLaneBuckets((string) $itemData->classification);

            $joined = $buckets[$assign[$itemData->id] ?? ''] ?? [];

            if (count($joined) > 1) {
                return $joined;
            }

            // Unparseable names keep the routing from before armor lines
            // existed (the Char_ stem route serves clothing-shaped armor
            // such as "Carnifex Pants").
            $fallback = str_starts_with((string) $itemData->type, 'Char_')
                ? $this->findVariantGroupFromStem($itemData)
                : $this->findTagOrClassNameGroup($itemData);

            return array_values(array_filter(
                $fallback,
                fn (ItemData $member): bool => self::armorLine($member->classification, $member->name) === null
            ));
        }

        [$buckets, , $assign] = $this->armorLaneBuckets((string) $itemData->classification);

        $mine = $buckets[$assign[$itemData->id] ?? ''] ?? [];

        return count($mine) > 1 ? $mine : [];
    }

    private static function nameCarriesSlotWord(string $name, string $slot): bool
    {
        return preg_match('/(^|\s)'.preg_quote($slot, '/').'(\s|$)/i', $name) === 1;
    }

    /**
     * @return array<int, ItemData> [] when the item has no >=2-member component
     */
    private function findWeaponDomainGroup(ItemData $itemData): array
    {
        return $this->weaponDomainComponents()[$itemData->id] ?? [];
    }

    /**
     * Name-stem and anchored class-family buckets become cliques; groups
     * are their >=2-member connected components. Per-item lookups cannot
     * express this (priority is not merge).
     *
     * @return array<int, array<int, ItemData>> item id -> component members
     */
    private function weaponDomainComponents(): array
    {
        if ($this->weaponComponents !== null) {
            return $this->weaponComponents;
        }

        $domain = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('classification', 'like', 'FPS.Weapon%')
            ->where('is_player_relevant', true)
            ->with(['item'])
            ->orderBy('class_name')
            ->orderBy('id')
            ->get()->all();

        $stemById = [];
        $stemBuckets = [];
        $familyBuckets = [];

        foreach ($domain as $item) {
            [$stem, $kind] = $this->extractWeaponNameStemAndKind($item->name);
            $stemById[$item->id] = $stem;

            // NULL kind and NULL sub_type never equal a value, so null and ''
            // cannot collide across buckets.
            $bucketKey = $stem."\u{1F}".($kind ?? "\u{1E}")."\u{1F}"
                .((string) $item->manufacturer_id)."\u{1F}"
                .((string) $item->type)."\u{1F}".($item->sub_type ?? "\u{1E}");
            $stemBuckets[$bucketKey][] = $item;

            $family = $this->extractWeaponFamily($item->class_name ?? '');

            if ($family !== null) {
                $familyKey = $family."\u{1F}".((string) $item->manufacturer_id)."\u{1F}"
                    .((string) $item->type)."\u{1F}".($item->sub_type ?? "\u{1E}");
                $familyBuckets[$familyKey]['family'] = $family;
                $familyBuckets[$familyKey]['members'][] = $item;
            }
        }

        $unionBuckets = array_values($stemBuckets);

        foreach ($familyBuckets as ['family' => $family, 'members' => $members]) {
            $hasAnchor = false;
            $stems = [];

            foreach ($members as $member) {
                $hasAnchor = $hasAnchor || ($member->class_name ?? '') === $family;
                $stems[$stemById[$member->id]] = true;
            }

            // The anchor requirement dissolves buckets of sibling products
            // with no base among them (the five grin_multitool_01
            // attachments).
            if ($hasAnchor && count($stems) >= 2) {
                $unionBuckets[] = $members;
            }
        }

        $roots = self::rootAssignments($domain, $unionBuckets);

        $components = [];

        foreach ($domain as $item) {
            $components[$roots[$item->id]][] = $item;
        }

        $componentsById = [];

        foreach ($components as $members) {
            if (count($members) >= 2) {
                foreach ($members as $member) {
                    $componentsById[$member->id] = $members;
                }
            }
        }

        return $this->weaponComponents = $componentsById;
    }

    /**
     * Name grammar: "<stem> [quoted edition] <canonical kind>". A NULL
     * kind is its own bucket value.
     *
     * @return array{0: string, 1: string|null}
     */
    private function extractWeaponNameStemAndKind(?string $name): array
    {
        if ($name === null || $name === '') {
            return ['', null];
        }

        // CIG separates some tokens with U+00A0/U+202F only; \s misses them.
        $normalized = str_replace(["\u{00A0}", "\u{202F}"], ' ', $name);
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $normalized));

        // Quoted spans are editions; re-collapse so the gap they leave cannot
        // split stems ('PT3 "Deadfall" (3x Holographic)' -> 'PT3 (3x Holographic)').
        $normalized = (string) preg_replace('/"[^"]*"/', '', $normalized);
        $normalized = (string) preg_replace("/'[^']*'/", '', $normalized);
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $normalized));

        $kind = null;
        $stemTokens = [];

        if (preg_match('/\(\d+(?:\.\d+)?x(?: [^)]+)?\)$/u', $normalized, $zoom) === 1) {
            // A trailing zoom paren is the whole kind ('EE16 (16x Telescopic)', 'Theta Pro (8x)').
            $kind = $zoom[0];
            $stemTokens = $this->weaponNameTokens(substr($normalized, 0, -strlen($zoom[0])));
        } else {
            // Any other trailing paren is decoration ('(45 cap)', '(15 Cap)', '(Modified)').
            $tokens = $this->weaponNameTokens((string) preg_replace('/\([^)]*\)$/u', '', $normalized));

            // '(<weaponword>)?(Magazine|Battery|Canister|Refill)' canonicalizes
            // to the bare container word; the weapon word is absorbed, never
            // keyed ('Arrowhead Sniper Rifle Battery' -> stem 'Arrowhead',
            // kind 'Battery'; 'FS-9 Magazine (75 cap)' forced the weapon word
            // to be optional).
            if ($tokens !== [] && in_array($tokens[count($tokens) - 1], ['Magazine', 'Battery', 'Canister', 'Refill'], true)) {
                $kind = $tokens[count($tokens) - 1];
                $stemTokens = array_slice($tokens, 0, -1);

                $weaponWord = $this->matchWeaponKindSuffix($stemTokens);

                if ($weaponWord !== null) {
                    $stemTokens = array_slice($stemTokens, 0, -count(explode(' ', $weaponWord)));
                }
            } else {
                // 'Black' stays out of the color head set: the domain's only
                // Black-headed name, 'Black Prism (8x Telescopic)', is an
                // optics product name, not a colorway.
                if ($tokens !== [] && in_array($tokens[count($tokens) - 1], ['Red', 'Blue', 'Yellow', 'Green', 'Orange', 'Purple', 'Teal'], true)) {
                    array_pop($tokens);
                }

                $matched = $this->matchWeaponKindSuffix($tokens);

                if ($matched !== null) {
                    // Kind surface forms canonicalize onto their base kind
                    // ('Twin Shotgun' -> 'Shotgun', 'Laser Pointer' ->
                    // 'Pointer', 'Frag'/'Toy Pistol' -> 'Pistol').
                    $kindTokens = array_values(array_filter(
                        explode(' ', $matched),
                        static fn (string $token): bool => ! in_array($token, ['Laser', 'Twin', 'Assault', 'Frag', 'Toy', 'Combat'], true),
                    ));

                    // Lexicon entries never reduce to nothing; guard anyway.
                    $kind = $kindTokens === [] ? $matched : implode(' ', $kindTokens);
                    $stemTokens = array_slice($tokens, 0, -count(explode(' ', $matched)));
                } else {
                    $stemTokens = $tokens;
                }
            }
        }

        if ($stemTokens !== [] && in_array($stemTokens[0], ['Cyan', 'Pink', 'Green', 'Orange', 'Red', 'Yellow'], true)) {
            array_shift($stemTokens);
        }

        // Editions drop the damage word ('Quartz Energy SMG' vs 'Quartz
        // "GCD-Army" SMG'), so it is stripped from the stem while more than
        // one token remains.
        while (count($stemTokens) > 1 && in_array($stemTokens[count($stemTokens) - 1], ['Energy', 'Laser'], true)) {
            array_pop($stemTokens);
        }

        return [implode(' ', $stemTokens), $kind];
    }

    /** @return array<int, string> */
    private function weaponNameTokens(string $value): array
    {
        $tokens = preg_split('/\s+/u', trim($value)) ?: [];

        return array_values(array_filter($tokens, static fn (string $token): bool => $token !== ''));
    }

    /**
     * @param  array<int, string>  $tokens
     */
    private function matchWeaponKindSuffix(array $tokens): ?string
    {
        $best = null;

        foreach (self::WEAPON_KIND_LEXICON as $candidate) {
            $candidateTokens = explode(' ', $candidate);

            if (count($tokens) < count($candidateTokens)) {
                continue;
            }

            if ($candidateTokens !== array_slice($tokens, -count($candidateTokens))) {
                continue;
            }

            if ($best === null || strlen($candidate) > strlen($best)) {
                $best = $candidate;
            }
        }

        return $best;
    }

    /**
     * Class family: class_name cut at the first two-digit model index or
     * 's<N>' size segment (the cut segment stays); a class with no cut
     * segment is its own family.
     */
    private function extractWeaponFamily(string $className): ?string
    {
        if ($className === '') {
            return null;
        }

        $segments = explode('_', $className);

        // 'Carryable_1H_CU_Glowstick_*' carries colorway and product names
        // in one class family; the name grammar owns those.
        if (strcasecmp($segments[0], 'carryable') === 0) {
            return null;
        }

        foreach ($segments as $i => $segment) {
            if (preg_match('/^\d{2}$/', $segment) !== 1 && preg_match('/^s\d$/', $segment) !== 1) {
                continue;
            }

            $family = array_slice($segments, 0, $i + 1);

            // Tail segments stay after the cut anchor ('behr_rifle_ballistic_02_mag'
            // reproduces only when the 'mag' after the '02' anchor is included).
            if (in_array($segments[$i + 1] ?? null, ['mag', 'missile', 'rocket'], true)) {
                $family[] = $segments[$i + 1];
            }

            return implode('_', $family);
        }

        return $className;
    }

    /** @return array<int,ItemData> */
    private function findVariantGroupFromStem(ItemData $itemData): array
    {
        $stem = $this->extractNameStem($itemData);

        if ($stem === null) {
            return [];
        }

        $component = $this->stemPartition($this->stemBucketPeers($itemData, $stem), $itemData);

        if ($component === null) {
            return [];
        }

        $merged = $this->mergeComponentsByLeaf($component);

        return count($merged) > 1 ? $merged : [];
    }

    private function extractNameStem(ItemData $itemData): ?string
    {
        $name = $itemData->name;

        if ($name === null || $name === '') {
            return null;
        }

        // CIG sometimes uses U+00A0 as the only separator; \s misses it.
        $normalized = str_replace(["\u{00A0}", "\u{202F}"], ' ', $name);
        $tokens = preg_split('/\s+/u', trim($normalized)) ?: [];
        $tokens = array_values(array_filter($tokens, static fn (string $token): bool => $token !== ''));

        // A lone word is not a line identity: 75 piercings are all named "Ball".
        if (count($tokens) < 2) {
            return null;
        }

        return implode(' ', array_slice($tokens, 0, 2));
    }

    /** @return array<int, ItemData> */
    private function stemBucketPeers(ItemData $itemData, string $stem): array
    {
        $cacheKey = 'stem|'.$stem.'|'.$itemData->manufacturer_id.'|'.$itemData->type.'|'.($itemData->sub_type ?? '\N');
        $peers = $this->cachedQuery($cacheKey, fn (): Builder => $this->stemBucketQuery($itemData));

        $peers = array_values(array_filter($peers, function (ItemData $peer) use ($stem): bool {
            return $peer->is_player_relevant && $this->extractNameStem($peer) === $stem;
        }));

        return $peers;
    }

    private function stemBucketQuery(ItemData $itemData): Builder
    {
        $query = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('manufacturer_id', $itemData->manufacturer_id)
            ->where('type', $itemData->type);

        if ($itemData->sub_type === null) {
            $query->whereNull('sub_type');
        } else {
            $query->where('sub_type', $itemData->sub_type);
        }

        return $query;
    }

    /**
     * @param  array<int, ItemData>  $bucket
     * @return array<int, ItemData>|null
     */
    private function stemPartition(array $bucket, ItemData $subject): ?array
    {
        if ($bucket === []) {
            return null;
        }

        $leavesById = [];
        foreach ($bucket as $member) {
            $leavesById[$member->id] = $this->setTagTaxonomy()->roleLeafNames($member, 'Set');
        }

        $parent = [];
        foreach ($bucket as $member) {
            $parent[$member->id] = $member->id;
        }

        $tagged = array_values(array_filter($bucket, static fn (ItemData $member): bool => $leavesById[$member->id] !== []));

        $pairBuckets = [];
        for ($i = 0, $max = count($tagged); $i < $max; $i++) {
            for ($j = $i + 1; $j < $max; $j++) {
                if (array_intersect_key($leavesById[$tagged[$i]->id], $leavesById[$tagged[$j]->id]) !== []) {
                    $pairBuckets[] = [$tagged[$i], $tagged[$j]];
                }
            }
        }

        $rootsById = self::rootAssignments($tagged, $pairBuckets);

        $components = [];
        foreach ($tagged as $member) {
            $components[$rootsById[$member->id]][] = $member;
        }

        $majorityRoot = null;
        $majoritySize = 0;
        $majorityLabel = null;
        foreach ($components as $root => $members) {
            $label = min(array_map(static fn (ItemData $member): string => min($leavesById[$member->id]), $members));

            if (count($members) > $majoritySize || (count($members) === $majoritySize && $majorityLabel !== null && $label < $majorityLabel)) {
                $majorityRoot = $root;
                $majoritySize = count($members);
                $majorityLabel = $label;
            }
        }

        $subjectComponent = [];
        $subjectRoot = $leavesById[$subject->id] !== [] ? $rootsById[$subject->id] : $majorityRoot;

        foreach ($bucket as $member) {
            $root = $leavesById[$member->id] !== [] ? $rootsById[$member->id] : $majorityRoot;

            if ($root === $subjectRoot) {
                $subjectComponent[] = $member;
            }
        }

        // Singletons survive; the leaf merge can still reassemble fragments.
        return $subjectComponent;
    }

    /**
     * CIG tags some colorways into differently named lines ("Citadel Arms
     * Icefall" is Citadel-SE), so components sharing exactly one Set leaf
     * sweep in same-bucket peers carrying that leaf.
     *
     * @param  array<int, ItemData>  $component
     * @return array<int, ItemData>
     */
    private function mergeComponentsByLeaf(array $component): array
    {
        $partTagUuids = $this->setTagTaxonomy()->roleRowUuids('Part');

        if ($partTagUuids === []) {
            Log::warning('game_entity_tags has no "Part" rows; cross-stem leaf merge disabled');

            return $component;
        }

        $common = null;
        foreach ($component as $member) {
            $leaves = $this->setTagTaxonomy()->roleLeafNames($member, 'Set');

            if ($leaves === []) {
                continue;
            }

            $common = $common === null ? $leaves : array_intersect_key($common, $leaves);
        }

        if ($common === null || count($common) !== 1) {
            return $component;
        }

        $leafUuid = (string) key($common);

        // CIG doubles some manufacturers into a same-named Set leaf (Artimex
        // and Testudo both carry Set leaf "QuirinusTech").
        foreach ($component as $member) {
            if (in_array($common[$leafUuid], $this->setTagTaxonomy()->roleLeafNames($member, 'Manufacturer'), true)) {
                return $component;
            }
        }

        $partNames = [];
        foreach ($component as $member) {
            foreach ($this->setTagTaxonomy()->roleLeafNames($member, 'Part') as $name) {
                $partNames[$name] = true;
            }
        }

        if (count($partNames) > 1) {
            return $component;
        }

        $partName = $partNames === [] ? null : array_key_first($partNames);
        $reference = $component[0];

        $merged = [];
        foreach ($component as $member) {
            $merged[$member->id] = $member;
        }

        $peers = $this->cachedQuery(
            'stemleaf|'.$leafUuid.'|'.$reference->manufacturer_id.'|'.$reference->type.'|'.($reference->sub_type ?? '\N'),
            fn (): Builder => $this->stemBucketQuery($reference)
        );

        foreach ($peers as $peer) {
            if (! $peer->is_player_relevant || isset($merged[$peer->id])) {
                continue;
            }

            if (! isset($this->setTagTaxonomy()->roleLeafNames($peer, 'Set')[$leafUuid])) {
                continue;
            }

            $peerPartNames = array_values($this->setTagTaxonomy()->roleLeafNames($peer, 'Part'));

            $partsCompatible = $partName === null
                ? $peerPartNames === []
                : $peerPartNames === [$partName];

            if (! $partsCompatible) {
                continue;
            }

            $merged[$peer->id] = $peer;
        }

        $result = array_values($merged);
        usort($result, static fn (ItemData $a, ItemData $b): int => [$a->class_name ?? '', $a->id] <=> [$b->class_name ?? '', $b->id]);

        return $result;
    }

    /** @return array<int,ItemData> */
    private function findVariantGroupFromTags(ItemData $itemData): array
    {
        $tags = $this->extractStdItemTags($itemData);
        $groupSignature = $this->resolveVariantGroupSignature($tags);

        if ($groupSignature === null) {
            return [];
        }

        $sig = implode(',', array_map(strtolower(...), $groupSignature));
        $cacheKey = sprintf('tag|%d|%s|%s', $this->gameVersionId, $sig, $this->buildTypeFilterSuffix($itemData));

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

    private function resolveVariantGroupSignature(array $tags): ?array
    {
        $signatureTags = [];

        foreach ($tags as $tag) {
            if (stripos($tag, 'set_') !== 0 && stripos($tag, 'color_') !== 0 && ! $this->isIgnoredVariantTag($tag)) {
                $signatureTags[] = $tag;
            }
        }

        if ($signatureTags === []) {
            return null;
        }

        return $signatureTags;
    }

    private function isIgnoredVariantTag(string $tag): bool
    {
        $lower = strtolower($tag);

        return str_starts_with($lower, 'sm_')
            || str_starts_with($lower, '$')
            || in_array($lower, ['flightready', 'helmet', 'missionquestitem', 'weaponmountusable'], true);
    }

    private function filterByClassNamePrefix(ItemData $itemData, array $results): array
    {
        $prefix = $this->extractClassNamePrefix($itemData->class_name ?? '');

        if ($prefix === null) {
            $prefix = $this->resolveFallbackPrefix($itemData->class_name ?? '');
        }

        if ($prefix === null) {
            // No derivable class identity: the raw tag pool spans the whole
            // domain, so anchor nothing rather than everything.
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

    /** @return array<int,ItemData> */
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

        $cacheKey = sprintf('cn|%d|%s|%s', $this->gameVersionId, $prefix, $this->buildTypeFilterSuffix($itemData));
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

    private function resolveFallbackPrefix(string $className): ?string
    {
        $segments = explode('_', $className);

        return count($segments) > 2
            ? implode('_', array_slice($segments, 0, -1))
            : null;
    }

    private function setTagTaxonomy(): SetTagTaxonomy
    {
        return $this->taxonomy ??= SetTagTaxonomy::fromDatabase();
    }

    /**
     * The member's own label from its own name parse: the colorway its
     * lane slot word leaves behind. Groups whose set_name is a longer
     * line than some members carry mislabel those members when stripped
     * against the set_name ("Zeus Exploration Suit" next to "Zeus
     * Exploration Helmet Starscape" -> "Starscape"). A quoted segment is
     * CIG's own edition marker ("Novikov \"Expo\" Backpack" -> "Expo").
     *
     * @param  array{uuid?: string, name?: string, classification?: string}|null  $member
     */
    private static function memberOwnColorway(?array $member): ?string
    {
        if ($member === null) {
            return null;
        }

        $name = $member['name'] ?? null;

        if ($name === null || $name === '') {
            return null;
        }

        if (preg_match('/"([^"]+)"/u', $name, $m) === 1
            && isset(self::ARMOR_LANE_SLOT[(string) ($member['classification'] ?? '')])) {
            return trim($m[1]);
        }

        $line = self::armorLine($member['classification'] ?? null, $name);

        if ($line === null) {
            return null;
        }

        $colorway = self::armorColorway($member['classification'] ?? null, $name, $line);

        return ($colorway !== null && $colorway !== '') ? $colorway : null;
    }

    /**
     * Within the tag-anchored subfamily, drop the leading slot word when
     * it dominates; members whose label does not carry it keep their
     * full original name, quote-wrapped so downstream normalizeVariantName
     * treats them as verbatim.
     *
     * @param  array<string, string>  $map  uuid => label, modified in place
     * @param  array<string, string>  $originalNames  uuid => original name
     * @param  array<string, bool>  $preStrippedUuids
     * @param  array<string, bool>  $ownLabeled  uuids labeled from their own name parse;
     *                                           the restore fallback must not overwrite them
     */
    private static function stripMajorityLeadingWord(array &$map, ?array $base, array $preStrippedUuids, array $originalNames, array $ownLabeled = []): void
    {
        $baseUuid = $base['uuid'] ?? null;
        $counts = [];
        $pool = 0;

        foreach ($map as $uuid => $label) {
            if ($uuid === $baseUuid || ! isset($preStrippedUuids[$uuid])) {
                continue;
            }

            if (preg_match('/^(\S+)\s+\S/', $label, $m) !== 1) {
                continue;
            }

            // Only slot words are strippable. Without this gate any paired
            // modifier ("Dark Red", "Dark Green") forms a 2/2 majority and
            // the quote-restore fallback then resets every clean label back
            // to its full original name (seen on Venture Arms).
            if (! in_array($m[1], self::SLOT_WORDS, true)) {
                continue;
            }

            $counts[$m[1]] = ($counts[$m[1]] ?? 0) + 1;
            $pool++;
        }

        if ($pool < 2 || $counts === []) {
            return;
        }

        arsort($counts);
        $word = array_key_first($counts);
        $second = array_slice($counts, 1, 1);

        if ($second !== [] && reset($second) === $counts[$word]) {
            return; // tie: no dominant slot word
        }

        if ($counts[$word] < $pool * 2 / 3) {
            return;
        }

        $prefix = $word.' ';

        foreach ($map as $uuid => $label) {
            if ($uuid === $baseUuid || ! isset($preStrippedUuids[$uuid]) || isset($ownLabeled[$uuid])) {
                continue;
            }

            $remainder = str_starts_with($label, $prefix) ? trim(mb_substr($label, strlen($prefix))) : '';

            $map[$uuid] = $remainder !== ''
                ? $remainder
                : '"'.($originalNames[$uuid] ?? $label).'"';
        }
    }

    private static function stripCommonSuffix(array &$map): void
    {
        $values = array_filter($map, fn (string $v): bool => $v !== 'Base' && $v !== '');

        if (count($values) < 2) {
            return;
        }

        $reversed = array_map(static fn (string $s): string => strrev($s), array_values($values));
        $lcp = self::longestCommonPrefix($reversed);
        $suffix = $lcp !== null ? strrev($lcp) : null;

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

    private static function trimTrailingSlotOrTypeWord(string $name): ?string
    {
        $name = trim($name);
        $parts = preg_split('/\s+/u', $name) ?: [];

        if (count($parts) < 2) {
            return null;
        }

        $last = (string) end($parts);
        $lastLower = mb_strtolower($last);

        if (! in_array($lastLower, ['helmet', 'arms', 'legs', 'core', 'undersuit', 'backpack', 'rifle'], true)) {
            return null;
        }

        array_pop($parts);
        $trimmed = trim(implode(' ', $parts));

        return $trimmed !== '' ? $trimmed : null;
    }

    private function extractStdItemTags(ItemData $itemData): array
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

    private function buildTypeFilterSuffix(ItemData $itemData): string
    {
        if ($itemData->classification !== null) {
            return strtolower($itemData->classification);
        }

        return strtolower((string) $itemData->type).'|'.strtolower((string) $itemData->sub_type);
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

    private static function nullIfEmpty(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    /** Union-find root with path halving, shared by both partition passes. */
    /**
     * Union-find over item ids: every member seeds as its own root, then
     * bucket mates merge onto their bucket head.
     *
     * @param  array<int, ItemData>  $members
     * @param  array<int, array<int, ItemData>>  $buckets
     * @return array<int, int> item id -> root id
     */
    private static function rootAssignments(array $members, array $buckets): array
    {
        $parent = [];

        foreach ($members as $member) {
            $parent[$member->id] = $member->id;
        }

        foreach ($buckets as $bucket) {
            foreach (array_slice($bucket, 1) as $member) {
                $parent[self::findRoot($parent, $member->id)] = self::findRoot($parent, $bucket[0]->id);
            }
        }

        $roots = [];

        foreach ($members as $member) {
            $roots[$member->id] = self::findRoot($parent, $member->id);
        }

        return $roots;
    }

    private static function findRoot(array &$parent, int $id): int
    {
        while ($parent[$id] !== $id) {
            $parent[$id] = $parent[$parent[$id]];
            $id = $parent[$id];
        }

        return $id;
    }

    /** @return array<int, ItemData> */
    private function cachedQuery(string $cacheKey, callable $queryBuilder, int $minCount = 1): array
    {
        if (array_key_exists($cacheKey, $this->queryCache)) {
            return $this->queryCache[$cacheKey];
        }

        // 'gameVersion' has no reader anywhere; eager-loading it costs one
        // identical query per fetch.
        $results = $queryBuilder()->with(['item'])
            ->orderBy('class_name')
            ->orderBy('id')
            ->get()->all();

        if (count($results) < $minCount) {
            $results = [];
        }

        return $this->queryCache[$cacheKey] = $results;
    }
}
