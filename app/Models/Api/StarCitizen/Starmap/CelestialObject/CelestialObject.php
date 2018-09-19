<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 04.08.2018 20:11
 */

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;

/**
 * CelestialObject Model
 */
class CelestialObject extends HasTranslations
{
    use ModelChangelog;

    protected $fillable = [
        'code',
        'exclude',
        'cig_id',
        'starsystem_id',
        'cig_time_modified',
        'type',
        'designation',
        'name',
        'age',
        'distance',
        'latitude',
        'longitude',
        'axial_tilt',
        'orbit_period',
        'description',
        'info_url',
        'habitable',
        'fairchanceact',
        'appearance',
        'sensor_population',
        'sensor_economy',
        'sensor_danger',
        'size',
        'parent_id',
        'subtype_id',
        'affiliation_id',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(CelestialObjectTranslation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo celestial_object_subtype
     */
    public function celestialObjectSubtype()
    {
        return $this->belongsTo(CelestialObjectSubtype::class, 'subtype_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Affiliation
     */
    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Starsystem
     */
    public function starsystem()
    {
        return $this->belongsTo(Starsystem::class, 'id');
    }

    /**
     * Hardcoded to fix Child Problems
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'id';
    }
}
