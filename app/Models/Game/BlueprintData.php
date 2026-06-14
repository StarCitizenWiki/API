<?php

declare(strict_types=1);

namespace App\Models\Game;

use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Mission\MissionBlueprint;
use App\Models\Game\Mission\MissionData;
use Database\Factories\Game\BlueprintDataFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlueprintData extends Model
{
    /** @use HasFactory<BlueprintDataFactory> */
    use HasFactory;

    use HasGameVersion;

    protected $table = 'game_blueprint_data';

    protected $perPage = 50;

    protected $fillable = [
        'blueprint_id',
        'game_version_id',
        'key',
        'category_uuid',
        'output_item_uuid',
        'output_name',
        'output_class',
        'craft_time_seconds',
        'is_available_by_default',
        'ingredient_resource_type_uuids',
        'unlocking_missions_count',
        'data',
    ];

    protected $casts = [
        'blueprint_id' => 'integer',
        'game_version_id' => 'integer',
        'craft_time_seconds' => 'integer',
        'is_available_by_default' => 'boolean',
        'ingredient_resource_type_uuids' => 'array',
        'unlocking_missions_count' => 'integer',
        'data' => 'array',
    ];

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function outputItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'output_item_uuid', 'uuid');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Commodity::class, 'game_blueprint_data_ingredients', 'blueprint_data_id', 'resource_type_id')
            ->select(['game_commodities.id', 'game_commodities.uuid', 'game_commodities.name']);
    }

    public function dismantleReturns(): BelongsToMany
    {
        return $this->belongsToMany(Commodity::class, 'game_blueprint_data_dismantle_returns', 'blueprint_data_id', 'resource_type_id')
            ->withPivot('quantity_scu')
            ->select(['game_commodities.id', 'game_commodities.uuid', 'game_commodities.name']);
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(MissionData::class, 'game_mission_data_blueprint', 'blueprint_data_id', 'mission_data_id')
            ->withPivot(['pool_uuid', 'item_data_id', 'chance'])
            ->using(MissionBlueprint::class)
            ->select([
                'game_mission_data.id',
                'game_mission_data.mission_id',
                'game_mission_data.title',
                'game_mission_data.debug_name',
                'game_mission_data.reward_scope',
            ]);
    }

    public function scopeConsumesResourceType(Builder $query, string $resourceTypeUuid): Builder
    {
        return $query->whereHas('ingredients', static function (Builder $builder) use ($resourceTypeUuid): void {
            $builder->where('uuid', $resourceTypeUuid);
        });
    }

    /**
     * @param  array<int, string>  $resourceTypeUuids
     */
    public function scopeConsumesAnyResourceTypes(Builder $query, array $resourceTypeUuids): Builder
    {
        return $query->whereHas('ingredients', static function (Builder $builder) use ($resourceTypeUuids): void {
            $builder->whereIn('uuid', $resourceTypeUuids);
        });
    }

    public function scopeDismantleReturnsResourceType(Builder $query, string $resourceTypeUuid): Builder
    {
        return $query->whereHas('dismantleReturns', static function (Builder $builder) use ($resourceTypeUuid): void {
            $builder->where('uuid', $resourceTypeUuid);
        });
    }

    public function scopeForOutputItemUuid(Builder $query, string $outputItemUuid): Builder
    {
        return $query->where('output_item_uuid', $outputItemUuid);
    }

    public function scopeForOutputName(Builder $query, string $outputName): Builder
    {
        return $query->whereLike('output_name', "%{$outputName}%");
    }

    public function scopeForOutputClass(Builder $query, string $outputClass): Builder
    {
        return $query->whereLike('output_class', "%{$outputClass}%");
    }

    public function scopeSearchOutput(Builder $query, string $searchTerm): Builder
    {
        return $query->where(static function (Builder $builder) use ($searchTerm): void {
            $builder->whereLike('output_name', "%{$searchTerm}%")
                ->orWhereLike('output_class', "%{$searchTerm}%");

            if (Str::isUuid($searchTerm)) {
                $builder->orWhere('output_item_uuid', $searchTerm);
            }
        });
    }
}
