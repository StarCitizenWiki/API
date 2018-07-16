<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Size;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle Size Model
 */
class VehicleSize extends Model
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
        return $this->hasMany('App\Models\StarCitizen\Vehicle\Size\VehicleSizeTranslation');
    }
}
