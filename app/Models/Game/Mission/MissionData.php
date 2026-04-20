<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocationData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class MissionData extends Model
{
    use HasFactory;

    private const array GROUP_COLUMNS = [
        'game_version_id',
        'title',
        'generator_class',
        'mission_giver',
        'faction_id',
        'illegal',
        'blueprint_pool_uuid',
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
        'blueprint_drop_chance',
        'blueprint_pool_uuid',
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
        'blueprint_drop_chance' => 'float',
        'blueprint_pool_uuid' => 'string',
        'data' => AsCollection::class,
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
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
            ->withPivot(['pool_uuid', 'item_data_id'])
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

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', function (Builder $builder) use ($code): void {
                $builder->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', static function (Builder $builder): void {
            $builder->where('is_default', true);
        });
    }

    public function scopeExcludeUnreleased(Builder $query, bool $exclude = true): Builder
    {
        if ($exclude) {
            $query->where('not_for_release', false)
                ->where('work_in_progress', false);
        }

        return $query;
    }

    public function scopeGroupByTitle(Builder $query, int $gameVersionId): Builder
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($gameVersionId) {
            $q->whereIn('game_mission_data.id', function ($sub) use ($gameVersionId) {
                $sub->selectRaw('MIN(id)')
                    ->from('game_mission_data')
                    ->where('game_version_id', $gameVersionId)
                    ->whereNotNull('title')
                    ->where('title', '!=', '')
                    ->groupBy(...self::GROUP_COLUMNS);
            })->orWhere(function (Builder $q) {
                $q->whereNull('game_mission_data.title')->orWhere('game_mission_data.title', '');
            });
        });
    }

    public function scopeWithGroupedAggregates(Builder $query): Builder
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $query;
        }

        $aggregates = DB::table('game_mission_data')
            ->select([
                DB::raw('MIN(id) as representative_id'),
                DB::raw('COUNT(*) - 1 as variant_count'),
            ])
            ->groupBy(...self::GROUP_COLUMNS);

        $match = $this->groupMatchExpression();

        return $query
            ->leftJoinSub($aggregates, 'mission_group', 'mission_group.representative_id', '=', 'game_mission_data.id')
            ->addSelect([
                DB::raw('game_mission_data.*'),
                'mission_group.variant_count',
                DB::raw("(SELECT to_jsonb(array_agg(DISTINCT sys)) FROM (SELECT jsonb_array_elements_text(gmd2.star_systems) AS sys FROM game_mission_data gmd2 WHERE {$match}) sub WHERE sys IS NOT NULL) as grouped_star_systems"),
            ]);
    }

    private function groupMatchExpression(string $alias = 'gmd2'): string
    {
        return collect(self::GROUP_COLUMNS)
            ->map(fn (string $col) => "{$alias}.{$col} IS NOT DISTINCT FROM game_mission_data.{$col}")
            ->implode(PHP_EOL.'                AND ');
    }
}
