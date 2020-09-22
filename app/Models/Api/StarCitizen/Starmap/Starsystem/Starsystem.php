<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\Starsystem;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;

/**
 * Class Starsystem
 */
class Starsystem extends HasTranslations
{
    use ModelChangelog;

    protected $fillable = [
        'code',
        'exclude',
        'cig_id',
        'status',
        'cig_time_modified',
        'type',
        'name',
        'position_x',
        'position_y',
        'position_z',
        'info_url',
        'affiliation_id',
        'aggregated_size',
        'aggregated_population',
        'aggregated_economy',
        'aggregated_danger',
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(StarsystemTranslation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function celestialObject()
    {
        return $this->hasMany(CelestialObject::class);
    }

    /**
     * Star System Affiliation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }
}
