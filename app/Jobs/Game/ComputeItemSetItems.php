<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\ItemData;
use App\Services\ItemVariantResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComputeItemSetItems implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const array SET_PARTS = ['helmet', 'core', 'arms', 'legs', 'backpack'];

    public function __construct(private readonly int $gameVersionId) {}

    public function handle(): void
    {
        DB::table('game_item_set_items')
            ->whereIn('item_data_id', ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->select('id'))
            ->delete();

        $lineParsed = $this->computeLineSetItems();

        $this->computeStemSwapSetItems($lineParsed);
    }

    /**
     * Armor set items per item and lane:
     *
     * 1. Line rule (primary): the same line and colorway in the other
     *    lanes ("Venture Arms Tan" -> "Venture Core Tan"). A bare member
     *    pairs with "... Base" spellings.
     * 2. Tie-break: CIG ships name-identical shop SKUs whose only
     *    discriminator is the class suffix token after the family index
     *    ("TrueDef-Pro Legs" as _01_01_01, _01_01_microtech,
     *    _01_01_carrack). When several candidates tie, the one whose
     *    class name matches this item's slot-swapped class name wins,
     *    then the numerically indexed one, so a lane lists one partner.
     * 3. Index-matched fill: a lane the line rule leaves empty falls
     *    back to the slot-swapped class name. CIG names helmet lines
     *    differently from body lines within one set (Balor HCH helmets
     *    + ADP body, CBH-3 helmets + PAB-1 body); the class index is
     *    the colorway id there, and the suffix token is the edition id
     *    -- the same structural key the fill uses for colorway typos
     *    whose line spans lanes ("Rigteous").
     *
     * @return array<int, true> ids of items whose name carries a line
     */
    private function computeLineSetItems(): array
    {
        $resolver = new ItemVariantResolver($this->gameVersionId);
        [$families, $display] = $resolver->armorLineFamilies();

        $lineParsed = [];
        $items = [];
        $byColorway = [];

        foreach ($families as $lineKey => $members) {
            $line = $display[$lineKey];

            foreach ($members as $itemData) {
                // Cross-lane spellings ("ADP-mk4 Core Justified" in the
                // helmet lane) are culled from groups; they link in no
                // panel either -- the name cannot say which piece it is.
                if (ItemVariantResolver::isCrossLaneArmorSpelling($itemData->classification, $itemData->name)) {
                    continue;
                }

                $lineParsed[$itemData->id] = true;
                $items[$itemData->id] = $itemData;

                $colorway = ItemVariantResolver::armorColorway($itemData->classification, $itemData->name, $line);

                // "" and "Base" are the same colorway in CIG's naming.
                $colorwayKey = ($colorway === null || $colorway === '' || strcasecmp((string) $colorway, 'Base') === 0)
                    ? 'base'
                    : mb_strtolower((string) $colorway);

                $byColorway[$lineKey.'|'.$colorwayKey][] = $itemData;
            }
        }

        $primary = [];

        foreach ($byColorway as $members) {
            foreach ($members as $member) {
                foreach ($members as $partner) {
                    if ($member->id !== $partner->id
                        && $member->classification !== $partner->classification) {
                        $primary[$member->id][$partner->classification][] = $partner;
                    }
                }
            }
        }

        $classificationForPart = [];

        foreach (ItemVariantResolver::ARMOR_LANE_SLOT as $classification => $slotWord) {
            $classificationForPart[strtolower($slotWord)] = $classification;
        }

        $partOf = function (?string $className): ?string {
            foreach (self::SET_PARTS as $part) {
                if ($className !== null && str_contains($className, '_'.$part.'_')) {
                    return $part;
                }
            }

            return null;
        };

        // Colorway index and edition token: the class segments after
        // the lane word (["01","01","microtech"], ["01","01","07"]).
        // Every armor lane counts, not just the four swap parts --
        // "..._backpack_01_01_iae2022" carries the same edition token.
        $laneParts = array_values(array_map(
            static fn (string $slotWord): string => strtolower($slotWord),
            ItemVariantResolver::ARMOR_LANE_SLOT
        ));

        $remainderOf = function (?string $className) use ($laneParts): ?array {
            $segments = $className !== null ? explode('_', $className) : [];
            $partIndex = null;

            foreach ($segments as $index => $segment) {
                if (in_array($segment, $laneParts, true)) {
                    $partIndex = $index;

                    break;
                }
            }

            return $partIndex !== null ? array_slice($segments, $partIndex + 1) : null;
        };

        $swapLookup = ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->whereIn('classification', array_keys(ItemVariantResolver::ARMOR_LANE_SLOT))
            ->where('is_player_relevant', true)
            ->whereNotNull('class_name')
            ->get(['id', 'class_name', 'name', 'classification'])
            ->keyBy('class_name');

        $isLinkable = static fn (?ItemData $candidate): bool => $candidate !== null
            && ! ItemVariantResolver::isCrossLaneArmorSpelling($candidate->classification, $candidate->name);

        $inserts = [];

        foreach ($items as $item) {
            $itemPart = $partOf($item->class_name);
            $itemWords = preg_split('/[\s\/]+/u', (string) $item->name) ?: [];

            foreach (self::SET_PARTS as $part) {
                $classification = $classificationForPart[$part] ?? null;

                if ($classification === null) {
                    continue;
                }

                $lane = array_values(array_filter(
                    $primary[$item->id][$classification] ?? [],
                    $isLinkable
                ));

                if ($lane !== []) {
                    // Exactly one partner per lane. Rank: edition-token
                    // remainder match, shared name words (Stirling
                    // Exploration beats Stirling Hazard for the
                    // Exploration backpack), numerically indexed
                    // remainder (the base SKU represents name-identical
                    // editions), then a stable lexicographic order.
                    $itemRemainder = $remainderOf($item->class_name);

                    usort($lane, function (ItemData $a, ItemData $b) use ($itemRemainder, $remainderOf, $itemWords): int {
                        $rank = function (ItemData $c) use ($itemRemainder, $remainderOf, $itemWords): array {
                            $r = $remainderOf($c->class_name);
                            $words = preg_split('/[\s\/]+/u', (string) $c->name) ?: [];

                            return [
                                ($r !== null && $itemRemainder !== null && $r === $itemRemainder) ? 0 : 1,
                                -count(array_intersect($itemWords, $words)),
                                ($r !== null && preg_match('/^(?:\d+)(?:_(\d+))*$/', implode('_', $r)) === 1) ? 0 : 1,
                                $r !== null ? implode('_', $r) : '',
                            ];
                        };

                        return $rank($a) <=> $rank($b);
                    });

                    $inserts[] = [
                        'item_data_id' => $item->id,
                        'set_item_data_id' => $lane[0]->id,
                    ];

                    continue;
                }

                if ($itemPart === null || $part === $itemPart) {
                    continue;
                }

                $expected = Str::replaceFirst('_'.$itemPart.'_', '_'.$part.'_', (string) $item->class_name);
                $found = $swapLookup[$expected] ?? null;

                if ($isLinkable($found) && $found->id !== $item->id) {
                    $inserts[] = [
                        'item_data_id' => $item->id,
                        'set_item_data_id' => $found->id,
                    ];
                }
            }
        }

        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('game_item_set_items')->insert($chunk);
        }

        return $lineParsed;
    }

    /**
     * Fallback for names without a line and for non-armor stems: link
     * class names that differ only in the slot segment. Items already
     * handled by the line rule are skipped -- the stem swap never
     * overrides a name-based decision.
     *
     * @param  array<int, true>  $lineParsed
     */
    private function computeStemSwapSetItems(array $lineParsed): void
    {
        ItemData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->where('is_player_relevant', true)
            ->whereNotNull('class_name')
            ->chunkById(500, function (Collection $items) use ($lineParsed): void {
                $parts = self::SET_PARTS;
                $candidates = [];

                foreach ($items as $itemData) {
                    if (isset($lineParsed[$itemData->id])
                        || ItemVariantResolver::isCrossLaneArmorSpelling($itemData->classification, $itemData->name)) {
                        continue;
                    }

                    $currentPart = null;

                    foreach ($parts as $part) {
                        if (str_contains((string) $itemData->class_name, '_'.$part.'_')) {
                            $currentPart = $part;

                            break;
                        }
                    }

                    if ($currentPart === null) {
                        continue;
                    }

                    foreach ($parts as $part) {
                        if ($part === $currentPart) {
                            continue;
                        }

                        $candidates[] = [
                            'item_data_id' => $itemData->id,
                            'candidate_class_name' => Str::replaceFirst('_'.$currentPart.'_', '_'.$part.'_', (string) $itemData->class_name),
                        ];
                    }
                }

                if ($candidates === []) {
                    return;
                }

                $classNames = array_column($candidates, 'candidate_class_name');

                $lookup = ItemData::query()
                    ->whereIn('class_name', $classNames)
                    ->where('game_version_id', $this->gameVersionId)
                    ->pluck('id', 'class_name');

                $inserts = [];

                foreach ($candidates as $candidate) {
                    $found = $lookup[$candidate['candidate_class_name']] ?? null;

                    if ($found !== null && $found !== $candidate['item_data_id']) {
                        $inserts[] = [
                            'item_data_id' => $candidate['item_data_id'],
                            'set_item_data_id' => $found,
                        ];
                    }
                }

                if ($inserts !== []) {
                    DB::table('game_item_set_items')->insert($inserts);
                }
            });
    }
}
