<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.07.2018
 * Time: 13:34
 */

namespace App\Traits;

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
        return $this->hasMany('App\Models\StarCitizen\Vehicle\Ship\Ship');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Ground Vehicles
     */
    public function groundVehicles()
    {
        return $this->hasMany('App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle');
    }
}
