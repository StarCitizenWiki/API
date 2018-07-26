<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ManufacturerTranslation::class);
    }
}
