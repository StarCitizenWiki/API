<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Type;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle Type Model
 */
class VehicleType extends Model
{
    use VehicleRelations;

    protected $with = [
        'translations',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('App\Models\StarCitizen\Vehicle\Type\VehicleTypeTranslation');
    }
}
