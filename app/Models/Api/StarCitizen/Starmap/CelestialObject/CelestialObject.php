<?php
/**
 * User: Keonie
 * Date: 04.08.2018 20:11
 */

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Events\ModelUpdating;
use App\Models\Api\ModelChangelog;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;

/**
 * CelestialObject Model
 * @package App\Models\Api\StarCitizen\Starmap\CelestialObject
 */
class CelestialObject extends HasTranslations
{
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

    protected $casts = [
    ];

    protected $with = [
        'celestial_object_subtype',
        'affiliation',
        'starsystem',
        'translations',
    ];

    protected $perPage = 5;

    protected $table = 'celestial_object';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(CelestialObjectTranslation::class);
    }

    /**
     * The saved changes
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function changelogs()
    {
        return $this->morphMany(ModelChangelog::class, 'changelog');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo celestial_object_subtype
     */
    public function celestial_object_subtype()
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
     * Use CelestialObject Class in Children
     *
     * @return string
     */
    public function getMorphClass()
    {
        return self::class;
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