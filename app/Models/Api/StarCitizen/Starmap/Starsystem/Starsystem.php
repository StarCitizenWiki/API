<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\Starsystem;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Starsystem
 */
class Starsystem extends HasTranslations
{
    use ModelChangelog;

    protected $fillable = [
        'cig_id',
        'code',
        'status',
        'info_url',
        'name',
        'type',
        'position_x',
        'position_y',
        'position_z',
        'frost_line',
        'habitable_zone_inner',
        'habitable_zone_outer',
        'aggregated_size',
        'aggregated_population',
        'aggregated_economy',
        'aggregated_danger',
        'time_modified',
        'description',
        'affiliation',
    ];

    protected $with = [
        'affiliation',
        'translations',
    ];

    protected $perPage = 5;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $casts = [
        'position_x' => 'decimal:8',
        'position_y' => 'decimal:8',
        'position_z' => 'decimal:8',
        'frost_line' => 'decimal:8',
        'habitable_zone_inner' => 'decimal:8',
        'habitable_zone_outer' => 'decimal:8',
        'aggregated_size' => 'decimal:8',
        'aggregated_population' => 'decimal:8',
        'aggregated_economy' => 'decimal:8',
    ];

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(StarsystemTranslation::class);
    }

    /**
     * @return HasMany
     */
    public function celestialObject(): HasMany
    {
        return $this->hasMany(CelestialObject::class);
    }

    public function planets(): HasMany
    {
        return $this->celestialObject()->where('type', 'PLANET');
    }

    /**
     * Star System Affiliation
     * @return BelongsToMany
     */
    public function affiliation(): BelongsToMany
    {
        return $this->belongsToMany(Affiliation::class, 'starsystem_affiliation');
    }
}
