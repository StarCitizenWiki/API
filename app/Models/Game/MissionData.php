<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionData extends Model
{
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
        'star_systems' => 'array',
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
            ->withPivot(['source', 'pool_key', 'pool_purpose'])
            ->using(MissionStarmapLocation::class)
            ->withTimestamps();
    }

    public function blueprints(): BelongsToMany
    {
        return $this->belongsToMany(BlueprintData::class, 'game_mission_data_blueprint', 'mission_data_id', 'blueprint_data_id')
            ->withPivot(['chance', 'pool_uuid', 'item_uuid', 'item_name'])
            ->using(MissionBlueprint::class)
            ->withTimestamps();
    }

    public function unlocks(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'game_mission_data_mission_chain', 'mission_data_id', 'linked_mission_data_id')
            ->withPivot(['chain_type', 'group_index', 'tag_uuid', 'tag_name'])
            ->using(MissionChain::class)
            ->wherePivot('chain_type', 'unlock')
            ->withTimestamps();
    }

    public function prerequisites(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'game_mission_data_mission_chain', 'mission_data_id', 'linked_mission_data_id')
            ->withPivot(['chain_type', 'group_index', 'tag_uuid', 'tag_name'])
            ->using(MissionChain::class)
            ->wherePivot('chain_type', 'prerequisite')
            ->withTimestamps();
    }

    public function unlockedBy(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'game_mission_data_mission_chain', 'linked_mission_data_id', 'mission_data_id')
            ->withPivot(['chain_type', 'group_index', 'tag_uuid', 'tag_name'])
            ->using(MissionChain::class)
            ->wherePivot('chain_type', 'unlock')
            ->withTimestamps();
    }

    public function requiredBy(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'game_mission_data_mission_chain', 'linked_mission_data_id', 'mission_data_id')
            ->withPivot(['chain_type', 'group_index', 'tag_uuid', 'tag_name'])
            ->using(MissionChain::class)
            ->wherePivot('chain_type', 'prerequisite')
            ->withTimestamps();
    }

    public function haulingOrders(): HasMany
    {
        return $this->hasMany(MissionHaulingOrder::class);
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
}
