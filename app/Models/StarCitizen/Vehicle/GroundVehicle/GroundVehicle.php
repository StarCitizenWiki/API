<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\GroundVehicle;

use App\Models\StarCitizen\Vehicle\AbstractVehicle as Vehicle;

/**
 * Ground Vehicle Class
 */
class GroundVehicle extends Vehicle
{
    protected $fillable = [
        'cig_id',
        'name',
        'manufacturer_id',
        'production_status_id',
        'vehicle_size_id',
        'vehicle_type_id',
        'length',
        'beam',
        'height',
        'mass',
        'cargo_capacity',
        'min_crew',
        'max_crew',
        'scm_speed',
        'afterburner_speed',
        'chassis_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicleTranslation');
    }
}
