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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ComputeItemVariantGroups implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    public function __construct(
        private readonly int $gameVersionId,
    ) {}

    public function handle(): void
    {
        $resolver = new ItemVariantResolver($this->gameVersionId);

        $this->computeVariantGroups($resolver);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Compute item variant groups job failed', [
            'game_version_id' => $this->gameVersionId,
            'message' => $exception->getMessage(),
        ]);
    }

    private function computeVariantGroups(ItemVariantResolver $resolver): void
    {
        DB::transaction(function () use ($resolver): void {
            $hadPreviousGroups = VariantGroup::query()
                ->where('game_version_id', $this->gameVersionId)
                ->exists();

            VariantGroup::query()
                ->where('game_version_id', $this->gameVersionId)
                ->delete();

            if ($hadPreviousGroups) {
                ItemData::query()
                    ->where('game_version_id', $this->gameVersionId)
                    ->update(['base_id' => null]);
            }

            $processedIds = [];

            ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->where('is_player_relevant', true)
                ->with(['item', 'gameVersion'])
                ->chunkById(250, function (Collection $items) use ($resolver, &$processedIds): void {
                    foreach ($items as $itemData) {
                        if (isset($processedIds[$itemData->id])) {
                            continue;
                        }

                        $group = array_values(array_filter(
                            $this->resolveGroup($resolver, $itemData),
                            static fn (ItemData $member): bool => ! isset($processedIds[$member->id])
                                && $member->is_player_relevant
                                && ! self::isExcludedVariant($member),
                        ));

                        if (count($group) < 2) {
                            $this->updateBaseId($itemData, null);

                            continue;
                        }

                        $base = $resolver->resolveBaseForGroup($group);

                        foreach ($group as $member) {
                            $processedIds[$member->id] = true;
                        }

                        $this->persistGroup($resolver, $group, $base);
                    }

                    $resolver->clearCaches();
                });
        });
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

        if ($itemData->classification === 'Ship.Paints') {
            $paintGroup = $resolver->findVariantGroupFromPaint($itemData);

            if (count($paintGroup) > 1) {
                return $paintGroup;
            }
        }

        if (str_starts_with((string) $itemData->classification, 'Ship.')) {
            $shipGroup = $resolver->findShipComponentGroup($itemData);

            if (count($shipGroup) > 1) {
                return $shipGroup;
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

    private function persistGroup(ItemVariantResolver $resolver, array $group, ItemData $base): void
    {
        $setName = $resolver->resolveSetNameFromEntityTags($group);

        $names = array_map(static fn (ItemData $item): string => $item->name ?? '', $group);
        $baseInfo = ['uuid' => $base->item->uuid ?? '', 'name' => $base->name ?? ''];
        $groupInfo = array_map(static fn (ItemData $item): array => ['uuid' => $item->item->uuid ?? '', 'name' => $item->name ?? ''], $group);

        if ($setName === null) {
            [$setName, $variantNames] = ItemVariantResolver::computeSetNameAndVariantNames($names, $baseInfo, $groupInfo);
        } else {
            [, $variantNames] = ItemVariantResolver::computeSetNameAndVariantNames($names, $baseInfo, $groupInfo);
        }

        if ($setName === null) {
            $setName = $this->deriveSetNameFromSetItems($base);
        }

        $variantGroup = VariantGroup::query()->create([
            'game_version_id' => $this->gameVersionId,
            'set_name' => $setName,
        ]);

        $sortOrder = 0;

        foreach ($group as $itemData) {
            $isBase = $itemData->id === $base->id;

            $variantName = ItemVariantResolver::normalizeVariantName($variantNames[$itemData->item->uuid ?? ''] ?? null);

            if ($variantName === 'Base' && str_starts_with((string) $itemData->classification, 'Ship.')) {
                $variantName = $itemData->name;
            }

            VariantGroupItem::query()->create([
                'variant_group_id' => $variantGroup->id,
                'item_data_id' => $itemData->id,
                'variant_name' => $variantName,
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

    /**
     * Derive a set name from armor set items by swapping slot segments
     * in the class_name (e.g., _helmet_ -> _core_, _arms_, _legs_)
     * and finding the longest common prefix of the resulting names.
     */
    private function deriveSetNameFromSetItems(ItemData $itemData): ?string
    {
        $className = $itemData->class_name;

        if ($className === null) {
            return null;
        }

        $currentSlot = null;
        foreach (ComputeItemSetItems::SET_PARTS as $part) {
            if (str_contains($className, '_' . $part . '_')) {
                $currentSlot = $part;

                break;
            }
        }

        if ($currentSlot === null) {
            return null;
        }

        // Collect names of all set members (other slots)
        $names = [$itemData->name ?? ''];

        foreach (ComputeItemSetItems::SET_PARTS as $part) {
            if ($part === $currentSlot) {
                continue;
            }

            $candidateClassName = Str::replaceFirst('_' . $currentSlot . '_', '_' . $part . '_', $className);

            $found = ItemData::query()
                ->where('class_name', $candidateClassName)
                ->where('game_version_id', $this->gameVersionId)
                ->value('name');

            if ($found !== null) {
                $names[] = $found;
            }
        }

        $names = array_values(array_filter($names, static fn (string $n): bool => $n !== ''));

        if (count($names) < 2) {
            return null;
        }

        // Strip slot words to normalize across armor pieces
        $slotPattern = '/\\s+(' . implode('|', array_map(
            static fn (string $w): string => preg_quote($w, '/'),
            ItemVariantResolver::SLOT_WORDS,
        )) . ')\\s+/iu';

        $strippedNames = array_map(
            static fn (string $name): string => trim(preg_replace($slotPattern, ' ', $name) ?? $name),
            $names,
        );

        return ItemVariantResolver::deriveSetNameFromNames($strippedNames);
    }

    private const array EXCLUDED_VARIANT_SUFFIXES = [
        '_fps_balance',
        '_FPS_Balance',
        '_Cutlass_Steel',
        '_FakeHologram',
    ];

    private static function isExcludedVariant(ItemData $member): bool
    {
        $className = $member->class_name ?? '';

        return array_any(self::EXCLUDED_VARIANT_SUFFIXES, fn ($suffix) => str_ends_with($className, $suffix));
    }
}
