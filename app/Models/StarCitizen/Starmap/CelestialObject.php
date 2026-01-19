<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/**
 * CelestialObject Model
 */
class CelestialObject extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'starmap_celestial_objects';

    public array $translatable = ['translation'];

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
        'translation',
    ];

    protected $with = [
        'subtype',
        'affiliation',
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
     * Celestial object subtype
     *
     * @return BelongsTo subtype
     */
    public function subtype(): BelongsTo
    {
        return $this->belongsTo(CelestialObjectSubtype::class, 'subtype_id')->withDefault();
    }

    /**
     * Affiliation
     *
     * @return BelongsToMany Affiliation
     */
    public function affiliation(): BelongsToMany
    {
        return $this->belongsToMany(Affiliation::class, 'starmap_celestial_object_affiliation');
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
     */
    public function jumppoint(): ?Jumppoint
    {
        return Jumppoint::query()
            ->where('entry_id', $this->cig_id)
            ->orWhere('exit_id', $this->cig_id)
            ->first();
    }
}
