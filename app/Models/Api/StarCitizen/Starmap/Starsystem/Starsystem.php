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
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'position_x' => 'decimal:8',
        'position_y' => 'decimal:8',
        'position_z' => 'decimal:8',
        'frost_line' => 'decimal:8',
        'habitable_zone_inner' => 'decimal:8',
        'habitable_zone_outer' => 'decimal:8',
        'aggregated_size' => 'decimal:8',
        'aggregated_population' => 'decimal:8',
        'aggregated_economy' => 'decimal:8',
        'aggregated_danger' => 'decimal:8',
    ];

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
     * Jump points with this system as its entry
     *
     * @return HasManyThrough
     */
    public function jumppointEntry(): HasManyThrough
    {
        return $this->hasManyThrough(
            Jumppoint::class,
            CelestialObject::class,
            'starsystem_id',
            'entry_id',
            'cig_id',
            'cig_id',
        );
    }

    /**
     * Jump points with this system as its exit
     *
     * @return HasManyThrough
     */
    public function jumppointExit(): HasManyThrough
    {
        return $this->hasManyThrough(
            Jumppoint::class,
            CelestialObject::class,
            'starsystem_id',
            'exit_id',
            'cig_id',
            'cig_id',
        );
    }

    /**
     * All jump points
     *
     * @return Collection
     */
    public function jumppoints(): Collection
    {
        return $this->jumppointEntry->merge($this->jumppointExit);
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
