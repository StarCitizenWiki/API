<?php

declare(strict_types=1);

namespace App\Models\Game\Resource;

use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocationData;
use Database\Factories\Game\Resource\ResourceProviderFactory;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResourceProvider extends Model
{
    /** @use HasFactory<ResourceProviderFactory> */
    use HasFactory;

    protected $table = 'game_resource_providers';

    protected $fillable = [
        'game_version_id',
        'provider_name',
        'areas',
    ];

    protected $casts = [
        'areas' => AsCollection::class,
    ];

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class);
    }

    public function resourceLocations(): HasMany
    {
        return $this->hasMany(ResourceLocation::class);
    }

    public function starmapLocationData(): BelongsToMany
    {
        return $this->belongsToMany(
            StarmapLocationData::class,
            'game_resource_provider_starmap',
            'resource_provider_id',
            'starmap_location_data_id',
        );
    }
}
