<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
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
        'subtype',
        'affiliation',
        'translations',
    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $casts = [
        'age' => 'float',
        'axial_tilt' => 'float',
        'distance' => 'float',
        'fairchanceact' => 'boolean',
        'habitable' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'orbit_period' => 'float',
        'sensor_danger' => 'float',
        'sensor_economy' => 'float',
        'sensor_population' => 'float',
        'size' => 'float',

        'time_modified' => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(CelestialObjectTranslation::class);
    }

    /**
     * Celestial object subtype
     *
     * @return BelongsTo subtype
     */
    public function subtype(): BelongsTo
    {
        return $this->belongsTo(CelestialObjectSubtype::class, 'subtype_id');
    }

    /**
     * Affiliation
     *
     * @return BelongsToMany Affiliation
     */
    public function affiliation(): BelongsToMany
    {
        return $this->belongsToMany(Affiliation::class, 'celestial_object_affiliation');
    }

    /**
     * Starsystem
     *
     * @return BelongsTo Starsystem
     */
    public function starsystem(): BelongsTo
    {
        return $this->belongsTo(Starsystem::class, 'starsystem_id', 'cig_id');
    }

    /**
     * A jumppoint with its entry or exit id equal to this cig_id
     *
     * @return Jumppoint|null
     */
    public function jumppoint(): ?Jumppoint
    {
        return Jumppoint::query()
            ->where('entry_id', $this->cig_id)
            ->orWhere('exit_id', $this->cig_id)
            ->first();
    }
}
