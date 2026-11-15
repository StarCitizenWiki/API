<?php

declare(strict_types=1);

namespace App\Models\Game\Resource;

use App\Models\Game\HasGameVersion;
use App\Models\Game\StarmapLocationData;
use Database\Factories\Game\Resource\ResourceProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResourceProvider extends Model
{
    /** @use HasFactory<ResourceProviderFactory> */
    use HasFactory;

    use HasGameVersion;

    protected $table = 'game_resource_providers';

    protected $fillable = [
        'game_version_id',
        'provider_name',
        'areas',
    ];

    protected $casts = [
        'areas' => 'array',
    ];

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
