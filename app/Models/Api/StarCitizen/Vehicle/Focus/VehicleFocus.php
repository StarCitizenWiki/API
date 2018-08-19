<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Focus;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

/**
 * Vehicle Focus Model
 */
class VehicleFocus extends HasTranslations
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
        return $this->hasMany(VehicleFocusTranslation::class);
    }
}
