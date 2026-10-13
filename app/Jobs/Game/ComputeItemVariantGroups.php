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
                    ->whereNotNull('base_id')
                    ->update(['base_id' => null]);
            }

            $processedIds = [];

            ItemData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->where('is_player_relevant', true)
                ->with(['item'])
                ->chunkById(250, function (Collection $items) use ($resolver, &$processedIds): void {
                    foreach ($items as $itemData) {
                        if (isset($processedIds[$itemData->id])) {
                            continue;
                        }

                        $group = array_values(array_filter(
                            $resolver->resolveGroup($itemData),
                            function (ItemData $member) use ($processedIds): bool {
                                if (isset($processedIds[$member->id])
                                    || ! $member->is_player_relevant
                                    || self::isExcludedVariant($member)) {
                                    return false;
                                }

                                if (ItemVariantResolver::isCrossLaneArmorSpelling($member->classification, $member->name)) {
                                    Log::info('Dropped cross-lane armor spelling from variant group', [
                                        'class_name' => $member->class_name,
                                        'name' => $member->name,
                                        'classification' => $member->classification,
                                    ]);

                                    return false;
                                }

                                return true;
                            }
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

    private function persistGroup(ItemVariantResolver $resolver, array $group, ItemData $base): void
    {
        [$setName, $variantNames] = $this->resolveGroupNames($resolver, $group, $base);

        $variantGroup = VariantGroup::query()->create([
            'game_version_id' => $this->gameVersionId,
            'set_name' => $setName,
        ]);

        foreach ($group as $sortOrder => $itemData) {
            $this->persistGroupItem($variantGroup, $itemData, $base, $variantNames, $sortOrder);
        }
    }

    /**
     * Armor groups take their set name from the product line shared by
     * every member; tag resolution stays as the fallback for pools of
     * unparseable names, where no line exists. Mixed lines cannot reach
     * here (line pools are pure by construction), but degrade to the
     * tag path if they ever do.
     *
     * @param  array<int, ItemData>  $group
     * @return array{0: string|null, 1: array<string, string>}
     */
    private function resolveGroupNames(ItemVariantResolver $resolver, array $group, ItemData $base): array
    {
        $names = array_map(static fn (ItemData $item): string => $item->name ?? '', $group);
        $baseInfo = ['uuid' => $base->item->uuid ?? '', 'name' => $base->name ?? '', 'classification' => $base->classification];
        $groupInfo = array_map(static fn (ItemData $item): array => ['uuid' => $item->item->uuid ?? '', 'name' => $item->name ?? '', 'classification' => $item->classification], $group);

        $line = null;

        foreach ($group as $item) {
            $memberLine = $resolver->resolveArmorLine($item);

            if ($memberLine === null) {
                continue;
            }

            if ($line !== null && mb_strtolower($memberLine) !== mb_strtolower($line)) {
                $line = null;

                break;
            }

            $line ??= $memberLine;
        }

        $setName = $line ?? $resolver->resolveSetNameFromEntityTags($group);
        [, $variantNames] = ItemVariantResolver::computeSetNameAndVariantNames($names, $baseInfo, $groupInfo, $setName);

        return [$setName, $variantNames];
    }

    /** @param array<string, string> $variantNames */
    private function persistGroupItem(VariantGroup $variantGroup, ItemData $itemData, ItemData $base, array $variantNames, int $sortOrder): void
    {
        $isBase = $itemData->id === $base->id;

        VariantGroupItem::query()->create([
            'variant_group_id' => $variantGroup->id,
            'item_data_id' => $itemData->id,
            'variant_name' => $this->resolveVariantName($itemData, $variantNames),
            'sort_order' => $sortOrder,
            'is_base' => $isBase,
        ]);

        $this->updateBaseId($itemData, $isBase ? null : $base->id);
    }

    /** @param array<string, string> $variantNames */
    private function resolveVariantName(ItemData $itemData, array $variantNames): ?string
    {
        $variantName = ItemVariantResolver::normalizeVariantName($variantNames[$itemData->item->uuid ?? ''] ?? null);

        if ($variantName === 'Base' && str_starts_with((string) $itemData->classification, 'Ship.')) {
            return $itemData->name;
        }

        return $variantName;
    }

    private function updateBaseId(ItemData $itemData, ?int $baseId): void
    {
        if ($baseId !== $itemData->base_id) {
            ItemData::query()
                ->whereKey($itemData->id)
                ->update(['base_id' => $baseId]);
        }
    }

    private static function isExcludedVariant(ItemData $member): bool
    {
        $className = $member->class_name ?? '';

        // The Cutlass_Steel showroom hull would ride the real Cutlass pool.
        return str_ends_with($className, '_Cutlass_Steel');
    }
}
