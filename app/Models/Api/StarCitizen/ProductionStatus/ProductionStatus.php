<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionStatus;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

/**
 * Production Status Model
 */
class ProductionStatus extends HasTranslations
{
    use VehicleRelations;

    public $timestamps = false;

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ProductionStatusTranslation::class);
    }
}
