<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.07.2018
 * Time: 13:34
 */

namespace App\Traits;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle;
use App\Models\Api\StarCitizen\Vehicle\Ship;

/**
 * Trait HasModelTranslationsTrait
 */
trait HasVehicleRelationsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Ships
     */
    public function ships()
    {
        return $this->hasMany(Ship::class, 'vehicle_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Ground Vehicles
     */
    public function groundVehicles()
    {
        return $this->hasMany(GroundVehicle::class, 'vehicle_id');
    }
}
