<?php
/**
 * User: Keonie
 * Date: 04.08.2018 18:19
 */

namespace App\Models\Api\StarCitizen\Starmap\Starsystem;

use App\Events\ModelUpdating;
use App\Models\Api\ModelChangelog;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;

/**
 * Class Starsystem
 * @package App\Models\Api\StarCitizen\Starmap\Starsystem
 */
class Starsystem extends HasTranslations
{
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

    protected $casts = [
    ];

    protected $with = [
        'affiliation',
        'translations',
    ];

    protected $perPage = 5;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
    ];

    protected $table = 'starsystem';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(StarsystemTranslation::class);
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
     * Star System Affiliation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affiliation()
    {
        return $this->belongsTo(Affiliation::class, 'affiliation_id');
    }
}