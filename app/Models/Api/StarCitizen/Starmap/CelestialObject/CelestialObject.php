<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CelestialObject Model
 */
class CelestialObject extends HasTranslations
{
    use ModelChangelog;

    protected $fillable = [
        'cig_id',
        'starsystem_id',
        'age',
        'appearance',
        'axial_tilt',
        'code',
        'designation',
        'distance',
        'fairchanceact',
        'habitable',
        'info_url',
        'latitude',
        'longitude',
        'name',
        'orbit_period',
        'parent_id',
        'sensor_danger',
        'sensor_economy',
        'sensor_population',
        'size',
        'type',
        'subtype_id',
        'time_modified',
    ];

    protected $with = [
        'celestialObjectSubtype',
        'affiliation',
        'starsystem',
        'translations',
    ];

    protected $perPage = 5;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $casts = [
        'age' => 'decimal:8',
        'distance' => 'decimal:8',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'sensor_danger' => 'decimal:8',
        'sensor_economy' => 'decimal:8',
        'sensor_population' => 'decimal:8',
        'axial_tilt' => 'decimal:8',
        'orbit_period' => 'decimal:8',
        'size' => 'decimal:8',
    ];

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(CelestialObjectTranslation::class);
    }

    /**
     * @return BelongsTo celestial_object_subtype
     */
    public function celestialObjectSubtype(): BelongsTo
    {
        return $this->belongsTo(CelestialObjectSubtype::class, 'subtype_id');
    }

    /**
     * @return BelongsToMany Affiliation
     */
    public function affiliation(): BelongsToMany
    {
        return $this->belongsToMany(Affiliation::class, 'celestial_object_affiliation');
    }

    /**
     * @return BelongsTo Starsystem
     */
    public function starsystem(): BelongsTo
    {
        return $this->belongsTo(Starsystem::class, 'id');
    }
}
