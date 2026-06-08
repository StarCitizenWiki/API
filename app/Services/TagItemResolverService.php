<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Resolves items matching tag-based search criteria.
 *
 * Accepts groups of positive/negative tag UUIDs. Within a group, positive tags are AND'd. Groups are OR'd together.
 * Positive tag expansion treats descendants as alternatives for the original tag.
 *
 * Results are filtered to player-relevant, non-flair items of type Cargo, Container, or Misc/Utility only.
 */
class TagItemResolverService
{
    /**
     * Resolve ItemData IDs matching the given tag groups for a specific game version.
     *
     * @param  list<array{positive: list<string>, negative: list<string>}>  $groups
     * @return list<int>
     */
    public function resolveItemDataIds(array $groups, int $versionId): array
    {
        $unionQuery = null;

        foreach ($groups as $group) {
            $positiveTagUuids = $this->normalizeTagUuids($group['positive'] ?? []);
            $positiveTagIdSets = $this->expandTagIdSets($positiveTagUuids);
            $negativeTagIds = $this->expandTagIds($group['negative'] ?? []);

            if ($positiveTagUuids !== [] && count($positiveTagIdSets) !== count($positiveTagUuids)) {
                continue;
            }

            if ($positiveTagIdSets === [] && $negativeTagIds === []) {
                continue;
            }

            $groupQuery = $this->buildGroupQuery($positiveTagIdSets, $negativeTagIds, $versionId);

            if ($unionQuery === null) {
                $unionQuery = $groupQuery;

                continue;
            }

            $unionQuery->union($groupQuery);
        }

        if ($unionQuery === null) {
            return [];
        }

        return DB::query()
            ->fromSub($unionQuery, 'matched_items')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Resolve full ItemData models matching the given tag groups.
     *
     * @param  list<array{positive: list<string>, negative: list<string>}>  $groups
     * @return Collection<int, ItemData>
     */
    public function resolveItems(array $groups, int $versionId, array $with = ['item', 'manufacturer', 'variantGroupItem', 'gameVersion']): Collection
    {
        $ids = $this->resolveItemDataIds($groups, $versionId);

        if ($ids === []) {
            return new Collection;
        }

        return ItemData::query()
            ->where('game_version_id', $versionId)
            ->whereIn('game_item_data.id', $ids)
            ->orderBy('type')
            ->orderBy('sub_type')
            ->orderBy('name')
            ->with($with)
            ->get();
    }

    /**
     * Expand a list of tag UUIDs to include all descendant tag IDs using a recursive CTE over game_entity_tags.parent_uuid.
     *
     * @param  list<string>  $tagUuids
     * @return list<int> EntityTag IDs (including descendants)
     */
    private function expandTagIds(array $tagUuids): array
    {
        return collect($this->expandTagIdSets($tagUuids))
            ->flatten()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Expand tag UUIDs into one descendant ID set per original tag UUID.
     *
     * @param  list<string>  $tagUuids
     * @return list<list<int>>
     */
    private function expandTagIdSets(array $tagUuids): array
    {
        $tagUuids = $this->normalizeTagUuids($tagUuids);

        if ($tagUuids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($tagUuids), '?'));

        $rows = DB::select("
            WITH RECURSIVE tag_tree AS (
                SELECT uuid AS root_uuid, id, uuid, parent_uuid
                FROM game_entity_tags
                WHERE uuid IN ({$placeholders})
                UNION ALL
                SELECT parent.root_uuid, child.id, child.uuid, child.parent_uuid
                FROM game_entity_tags child
                JOIN tag_tree parent ON child.parent_uuid = parent.uuid
            )
            SELECT root_uuid, id FROM tag_tree
        ", $tagUuids);

        $idsByRootUuid = array_fill_keys($tagUuids, []);

        foreach ($rows as $row) {
            $idsByRootUuid[(string) $row->root_uuid][] = (int) $row->id;
        }

        return collect($tagUuids)
            ->map(fn (string $uuid): array => array_values(array_unique($idsByRootUuid[$uuid] ?? [])))
            ->filter(fn (array $ids): bool => $ids !== [])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function normalizeTagUuids(array $tagUuids): array
    {
        return collect($tagUuids)
            ->filter(fn (mixed $uuid): bool => is_string($uuid) && $uuid !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Build a query that returns item_data IDs matching a single group.
     *
     * Items must match each positive tag set and must not have any negative tags.
     * Groups are OR'd together at the union level.
     *
     * @param  list<list<int>>  $positiveTagIdSets
     * @param  list<int>  $negativeTagIds
     */
    private function buildGroupQuery(array $positiveTagIdSets, array $negativeTagIds, int $versionId): QueryBuilder
    {
        $query = DB::table('game_item_data')
            ->select('game_item_data.id')
            ->where('game_item_data.game_version_id', $versionId)
            ->where('game_item_data.is_player_relevant', true)
            ->whereRaw("LOWER(game_item_data.class_name) NOT LIKE ? ESCAPE '\\'", ['%\\_flair\\_%'])
            ->where(function (QueryBuilder $query): void {
                $query->whereIn('game_item_data.type', ['Cargo', 'Container'])
                    ->orWhere(function (QueryBuilder $query): void {
                        $query->where('game_item_data.type', 'Misc')
                            ->where('game_item_data.sub_type', 'Utility');
                    });
            });

        foreach ($positiveTagIdSets as $tagIds) {
            $query->whereExists(function (QueryBuilder $query) use ($tagIds): void {
                $query->selectRaw('1')
                    ->from('game_item_data_entity_tag as pivot')
                    ->whereColumn('pivot.item_data_id', 'game_item_data.id')
                    ->whereIn('pivot.entity_tag_id', $tagIds);
            });
        }

        if ($negativeTagIds !== []) {
            $query->whereNotExists(function (QueryBuilder $query) use ($negativeTagIds): void {
                $query->selectRaw('1')
                    ->from('game_item_data_entity_tag as excluded')
                    ->whereColumn('excluded.item_data_id', 'game_item_data.id')
                    ->whereIn('excluded.entity_tag_id', $negativeTagIds);
            });
        }

        return $query;
    }
}
