<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Manufacturer;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Manufacturer Model
 */
class Manufacturer extends HasTranslations
{
    use VehicleRelations;

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
}
