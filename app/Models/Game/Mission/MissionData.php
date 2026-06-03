<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Faction;
use App\Models\Game\HasGameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocationData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MissionData extends Model
{
    use HasFactory;
    use HasGameVersion;

    private const array GROUP_COLUMNS = [
        'game_version_id',
        'title',
        'generator_class',
        'mission_giver',
        'faction_id',
        'illegal',
        'mission_key',
    ];

    protected $table = 'game_mission_data';

    protected $perPage = 50;

    protected $fillable = [
        'mission_id',
        'game_version_id',
        'debug_name',
        'mission_type',
        'mission_type_uuid',
        'mission_giver',
        'title',
        'description',
        'faction_id',
        'generator_class',
        'entry_type',
        'handler_type',
        'illegal',
        'shareable',
        'once_only',
        'available_in_prison',
        'not_for_release',
        'work_in_progress',
        'calculated_reward',
        'rank_index',
        'min_crime_stat',
        'max_crime_stat',
        'time_to_complete_minutes',
        'reward_min',
        'reward_max',
        'reward_currency',
        'star_systems',
        'has_combat',
        'has_defend_objective',
        'enemy_count_min',
        'enemy_count_max',
        'reward_scope',
        'mission_key',
        'data',
    ];

    protected $casts = [
        'mission_id' => 'integer',
        'game_version_id' => 'integer',
        'faction_id' => 'integer',
        'illegal' => 'boolean',
        'shareable' => 'boolean',
        'once_only' => 'boolean',
        'available_in_prison' => 'boolean',
        'not_for_release' => 'boolean',
        'work_in_progress' => 'boolean',
        'calculated_reward' => 'boolean',
        'rank_index' => 'integer',
        'min_crime_stat' => 'integer',
        'max_crime_stat' => 'integer',
        'time_to_complete_minutes' => 'float',
        'reward_min' => 'integer',
        'reward_max' => 'integer',
        'has_combat' => 'boolean',
        'has_defend_objective' => 'boolean',
        'enemy_count_min' => 'integer',
        'enemy_count_max' => 'integer',
        'star_systems' => 'array',
        'mission_key' => 'string',
        'data' => AsCollection::class,
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class)
            ->select(['game_missions.id', 'game_missions.uuid', 'game_missions.slug']);
    }

    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class, 'faction_id');
    }

    public function starmapLocations(): BelongsToMany
    {
        return $this->belongsToMany(StarmapLocationData::class, 'game_mission_data_starmap_location', 'mission_data_id', 'starmap_location_data_id')
            ->withPivot('purpose');
    }

    public function blueprints(): BelongsToMany
    {
        return $this->belongsToMany(BlueprintData::class, 'game_mission_data_blueprint', 'mission_data_id', 'blueprint_data_id')
            ->withPivot(['pool_uuid', 'item_data_id', 'chance'])
            ->using(MissionBlueprint::class);
    }

    public function prerequisiteGroups(): HasMany
    {
        return $this->hasMany(MissionPrerequisiteGroup::class);
    }

    public function unlockGroups(): HasMany
    {
        return $this->hasMany(MissionUnlockGroup::class);
    }

    public function requiredByMissions(): HasMany
    {
        return $this->hasMany(MissionPrerequisiteGroupMission::class, 'linked_mission_data_id');
    }

    public function unlockedByGroups(): HasMany
    {
        return $this->hasMany(MissionUnlockGroupMission::class, 'linked_mission_data_id');
    }

    public function rewardItems(): BelongsToMany
    {
        return $this->belongsToMany(ItemData::class, 'game_mission_data_reward_item', 'mission_data_id', 'item_data_id')
            ->withPivot(['amount', 'send_to_home']);
    }

    public function commodities(): BelongsToMany
    {
        return $this->belongsToMany(Commodity::class, 'game_mission_data_commodity', 'mission_data_id', 'commodity_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(ItemData::class, 'game_mission_data_item', 'mission_data_id', 'item_data_id');
    }

    public function scopeGroupByTitle(Builder $query, int $gameVersionId): Builder
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $query;
        }

        $representatives = $this->groupRepresentativeSubquery($query, $gameVersionId);

        return $query->where(function (Builder $q) use ($representatives) {
            $q->whereIn('game_mission_data.id', $representatives)
                ->orWhere(function (Builder $q) {
                    $q->whereNull('game_mission_data.title')->orWhere('game_mission_data.title', '');
                });
        });
    }

    /**
     * @param  Collection<int, self>  $missions
     * @return array{grouped_star_systems: array<int, string>, variant_uuids: array<int, string>, variant_counts: array<int, int>}
     */
    public static function loadGroupedAggregates(Collection $missions): array
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || $missions->isEmpty()) {
            return ['grouped_star_systems' => [], 'variant_uuids' => [], 'variant_counts' => []];
        }

        $groupedMissions = $missions
            ->filter(static fn (self $mission): bool => $mission->title !== null && $mission->title !== '')
            ->values();

        if ($groupedMissions->isEmpty()) {
            return ['grouped_star_systems' => [], 'variant_uuids' => [], 'variant_counts' => []];
        }

        $groupsByKey = $groupedMissions->groupBy(
            static fn (self $mission): string => self::groupKey($mission->groupValues()),
        );

        $rows = DB::table('game_mission_data as gmd')
            ->join('game_missions as m', 'm.id', '=', 'gmd.mission_id')
            ->leftJoin(DB::raw('LATERAL jsonb_array_elements_text(gmd.star_systems) AS sys(value)'), DB::raw('true'), '=', DB::raw('true'))
            ->whereNotNull('gmd.title')
            ->where('gmd.title', '!=', '')
            ->where(function (QueryBuilder $query) use ($groupedMissions): void {
                $groupedMissions->each(function (self $mission) use ($query): void {
                    $query->orWhere(function (QueryBuilder $groupQuery) use ($mission): void {
                        foreach ($mission->groupValues('gmd') as $column => $value) {
                            $value === null
                                ? $groupQuery->whereNull($column)
                                : $groupQuery->where($column, $value);
                        }
                    });
                });
            })
            ->select([
                ...collect(self::GROUP_COLUMNS)->map(fn (string $column): string => "gmd.{$column}")->all(),
                DB::raw("COALESCE(to_jsonb(array_agg(DISTINCT sys.value) FILTER (WHERE sys.value IS NOT NULL)), '[]'::jsonb) as grouped_star_systems"),
                DB::raw("COALESCE(jsonb_agg(DISTINCT jsonb_build_object('id', gmd.id, 'uuid', m.uuid)) FILTER (WHERE m.uuid IS NOT NULL), '[]'::jsonb) as variant_missions"),
                DB::raw('COUNT(DISTINCT gmd.id) - 1 as variant_count'),
            ])
            ->groupBy(...collect(self::GROUP_COLUMNS)->map(fn (string $column): string => "gmd.{$column}")->all())
            ->get();

        $starSystems = [];
        $variantUuids = [];
        $variantCounts = [];

        foreach ($rows as $row) {
            $missionsForGroup = $groupsByKey->get(self::groupKeyFromRow($row), collect());

            $missionsForGroup->each(static function (self $mission) use ($row, &$starSystems, &$variantUuids, &$variantCounts): void {
                $starSystems[$mission->id] = (string) $row->grouped_star_systems;
                $variantUuids[$mission->id] = self::variantUuidsJson((string) $row->variant_missions, $mission->id);
                $variantCounts[$mission->id] = max(0, (int) $row->variant_count);
            });
        }

        return [
            'grouped_star_systems' => $starSystems,
            'variant_uuids' => $variantUuids,
            'variant_counts' => $variantCounts,
        ];
    }

    private function groupRepresentativeSubquery(Builder $query, int $gameVersionId): Builder
    {
        $subquery = clone $query;

        return $subquery
            ->withoutEagerLoads()
            ->reorder()
            ->select(DB::raw('MIN(game_mission_data.id)'))
            ->where('game_mission_data.game_version_id', $gameVersionId)
            ->whereNotNull('game_mission_data.title')
            ->where('game_mission_data.title', '!=', '')
            ->groupBy(...$this->qualifiedGroupColumns());
    }

    /**
     * @return array<int, string>
     */
    private function qualifiedGroupColumns(string $alias = 'game_mission_data'): array
    {
        return collect(self::GROUP_COLUMNS)
            ->map(fn (string $col): string => "{$alias}.{$col}")
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function groupValues(?string $alias = null): array
    {
        $values = [];

        foreach (self::GROUP_COLUMNS as $column) {
            $values[$alias === null ? $column : "{$alias}.{$column}"] = $this->{$column};
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private static function groupKey(array $values): string
    {
        return json_encode(array_values($values)) ?: '[]';
    }

    private static function groupKeyFromRow(object $row): string
    {
        $values = [];

        foreach (self::GROUP_COLUMNS as $column) {
            $values[$column] = $row->{$column};
        }

        return self::groupKey($values);
    }

    private static function variantUuidsJson(string $variantMissions, int $representativeId): string
    {
        $variants = json_decode($variantMissions, true);

        if (! is_array($variants)) {
            return '[]';
        }

        $uuids = collect($variants)
            ->filter(static fn (mixed $variant): bool => is_array($variant) && (int) ($variant['id'] ?? 0) !== $representativeId)
            ->pluck('uuid')
            ->filter(static fn (mixed $uuid): bool => is_string($uuid) && $uuid !== '')
            ->unique()
            ->values()
            ->all();

        return json_encode($uuids) ?: '[]';
    }
}
