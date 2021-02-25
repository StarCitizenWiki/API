<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Manufacturer;

use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Manufacturer Model
 */
class Manufacturer extends HasTranslations
{
    use VehicleRelations;
    use ModelChangelog;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,

    ];

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
    ];

    protected $with = [
        'translations',
    ];

    protected $withCount = [
        'ships',
        'vehicles',
    ];

    protected $perPage = 10;

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(ManufacturerTranslation::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'name_short';
    }

    /**
     * @return HasManyThrough
     */
    public function translationChangelogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\System\ModelChangelog::class,
            ManufacturerTranslation::class,
            'manufacturer_id',
            'changelog_id'
        )->where('changelog_type', ManufacturerTranslation::class);
    }
}
