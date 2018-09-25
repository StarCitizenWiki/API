<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Type;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

/**
 * Vehicle Type Model
 */
class Type extends HasTranslations
{
    use VehicleRelations;
    use ObfuscatedRouteKey;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'vehicle_types';

    /**
     * @var array
     */
    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(TypeTranslation::class);
    }
}
