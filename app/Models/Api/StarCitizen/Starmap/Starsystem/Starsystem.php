<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\Starsystem;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\Affiliation;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Models\Api\StarCitizen\Starmap\Jumppoint\Jumppoint;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'position_z' => 'float',
        'frost_line' => 'float',
        'habitable_zone_inner' => 'float',
        'habitable_zone_outer' => 'float',
        'aggregated_size' => 'float',
        'aggregated_population' => 'float',
        'aggregated_economy' => 'float',
        'aggregated_danger' => 'float',

        'time_modified' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(StarsystemTranslation::class);
    }

    /**
     * The celestial objects in this system
     *
     * @return HasMany
     */
    public function celestialObjects(): HasMany
    {
        return $this->hasMany(CelestialObject::class, 'starsystem_id', 'cig_id');
    }

    /**
     * Celestial objects with type 'PLANET'
     *
     * @return HasMany
     */
    public function planets(): HasMany
    {
        return $this->celestialObjects()->where('type', 'PLANET');
    }

    /**
     * All jump points
     *
     * @return Collection
     */
    public function jumppoints(): Collection
    {
        return Jumppoint::query()
            ->whereIn('entry_id', $this->celestialObjects->pluck('cig_id'))
            ->orWhereIn('exit_id', $this->celestialObjects->pluck('cig_id'))
            ->get();
    }

    /**
     * Star System Affiliation
     *
     * @return BelongsToMany
     */
    public function affiliation(): BelongsToMany
    {
        return $this->belongsToMany(Affiliation::class, 'starsystem_affiliation');
    }
}
