<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Size;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;

/**
 * Vehicle Size Model
 */
class VehicleSize extends HasTranslations
{
    use VehicleRelations;
    use ObfuscatedRouteKey;

    public $timestamps = false;

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(VehicleSizeTranslation::class);
    }
}
