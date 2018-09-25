<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Size;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;

/**
 * Vehicle Size Model
 */
class Size extends HasTranslations
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
    protected $table = 'vehicle_sizes';

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
        return $this->hasMany(SizeTranslation::class);
    }
}
