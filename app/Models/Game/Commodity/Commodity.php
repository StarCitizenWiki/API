<?php

declare(strict_types=1);

namespace App\Models\Game\Commodity;

use App\Models\Game\BlueprintData;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Resource\ResourceCommodity;
use App\Models\Game\Resource\ResourceData;
use Database\Factories\Game\Commodity\CommodityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Commodity extends Model
{
    /** @use HasFactory<CommodityFactory> */
    use HasFactory;

    protected $table = 'game_commodities';

    protected $fillable = [
        'uuid',
        'slug',
        'key',
        'name',
        'description',
        'refined_version_uuid',
        'refined_version_name',
        'validate_default_cargo_box',
        'has_default_cargo_containers',
        'tier',
        'box_sizes_scu',
        'quality_distribution_uuid',
        'quality_location_override_uuid',
        'instability',
        'resistance',
        'density_g_per_cc',
        'data',
    ];

    protected $casts = [
        'validate_default_cargo_box' => 'boolean',
        'has_default_cargo_containers' => 'boolean',
        'box_sizes_scu' => 'array',
        'images' => 'array',
        'uex_prices' => 'array',
        'data' => AsCollection::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function refinedVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'refined_version_uuid', 'uuid');
    }

    public function rawVersions(): HasMany
    {
        return $this->hasMany(self::class, 'refined_version_uuid', 'uuid');
    }

    public function resourceData(): BelongsToMany
    {
        return $this->belongsToMany(
            ResourceData::class,
            'game_resource_commodity',
            'commodity_id',
            'resource_data_id',
        )->using(ResourceCommodity::class)->withPivot([
            'weight',
            'min_percentage',
            'max_percentage',
            'probability',
            'quality_scale',
            'curve_exponent',
        ])->withTimestamps();
    }

    public function scopeMatchingLookup(Builder $query, string $searchTerm): Builder
    {
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        return $query->where(static function (Builder $builder) use ($searchTerm, $like): void {
            $builder->whereRaw("name {$like} ?", ['%'.$searchTerm.'%'])
                ->orWhereRaw("key {$like} ?", ['%'.$searchTerm.'%']);

            if (Str::isUuid($searchTerm)) {
                $builder->orWhere('uuid', $searchTerm);
            }
        });
    }

    public function blueprints(): BelongsToMany
    {
        return $this->belongsToMany(
            BlueprintData::class,
            'game_blueprint_data_ingredients',
            'resource_type_id',
            'blueprint_data_id',
        );
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemData::class,
            'game_item_data_commodity',
            'commodity_id',
            'item_data_id',
        );
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(
            MissionData::class,
            'game_mission_data_commodity',
            'commodity_id',
            'mission_data_id',
        );
    }
}
