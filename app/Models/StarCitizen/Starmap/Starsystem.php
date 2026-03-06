<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Spatie\Translatable\HasTranslations;

class Starsystem extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'starmap_starsystems';

    public array $translatable = ['translation'];

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
        'translation',
    ];

    protected $with = [
        'affiliation',
    ];

    protected $withCount = [
        'stars',
        'planets',
        'moons',
        'stations',
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

    public function getCodeAttribute($value): string
    {
        return trim((string) $value);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function celestialObjects(): HasMany
    {
        return $this->hasMany(CelestialObject::class, 'starsystem_id', 'cig_id');
    }

    public function stars(): HasMany
    {
        return $this->celestialObjects()->where('type', 'STAR');
    }

    public function planets(): HasMany
    {
        return $this->celestialObjects()->where('type', 'PLANET');
    }

    public function moons(): HasMany
    {
        return $this->celestialObjects()->where('type', 'SATELLITE');
    }

    public function stations(): HasMany
    {
        return $this->celestialObjects()->where('type', 'MANMADE');
    }

    public function jumppoints(): HasManyThrough
    {
        return $this->hasManyThrough(
            Jumppoint::class,
            CelestialObject::class,
            'starsystem_id',
            'entry_id',
            'cig_id',
            'cig_id'
        );
    }

    public function jumppointsAll(): Collection
    {
        return Jumppoint::query()
            ->whereIn('entry_id', $this->celestialObjects->pluck('cig_id'))
            ->orWhereIn('exit_id', $this->celestialObjects->pluck('cig_id'))
            ->get();
    }

    public function affiliation(): BelongsToMany
    {
        return $this->belongsToMany(Affiliation::class, 'starmap_starsystem_affiliation');
    }
}
