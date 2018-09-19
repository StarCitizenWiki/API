<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionNote;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;

/**
 * Production Note Model
 */
class ProductionNote extends HasTranslations
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
        return $this->hasMany(ProductionNoteTranslation::class);
    }
}
