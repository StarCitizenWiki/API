<?php

declare(strict_types=1);

namespace App\Models\Game\Resource;

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\StarmapLocationData;
use Database\Factories\Game\Resource\ResourceLocationFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResourceLocation extends Model
{
    /** @use HasFactory<ResourceLocationFactory> */
    use HasFactory;

    protected $table = 'game_resource_locations';

    protected $fillable = [
        'resource_data_id',
        'resource_provider_id',
        'group_name',
        'group_probability',
        'relative_probability',
        'resource_kind',
        'commodity_id',
        'quality_min',
        'quality_max',
        'quality_mean',
        'quality_stddev',
        'min_percentage',
        'max_percentage',
        'data',
    ];

    protected $casts = [
        'resource_kind' => ResourceKind::class,
        'group_probability' => 'decimal:6',
        'relative_probability' => 'decimal:10',
        'quality_min' => 'integer',
        'quality_max' => 'integer',
        'quality_mean' => 'integer',
        'quality_stddev' => 'integer',
        'min_percentage' => 'decimal:4',
        'max_percentage' => 'decimal:4',
        'data' => AsCollection::class,
    ];

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }

    public function resourceData(): BelongsTo
    {
        return $this->belongsTo(ResourceData::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ResourceProvider::class, 'resource_provider_id');
    }

    public function starmapLocationData(): BelongsToMany
    {
        return $this->belongsToMany(
            StarmapLocationData::class,
            'game_resource_location_placements',
            'resource_location_id',
            'starmap_location_data_id',
        );
    }
}
