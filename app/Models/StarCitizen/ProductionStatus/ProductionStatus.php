<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\ProductionStatus;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Production Status Model
 */
class ProductionStatus extends Model
{
    use VehicleRelations;

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('App\Models\StarCitizen\ProductionStatus\ProductionStatusTranslation');
    }
}
