<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\AbstractVehicle as Vehicle;

/**
 * Ship Model
 */
class Ship extends Vehicle
{
    protected $fillable = [
        'cig_id',
        'name',
        'manufacturer_id',
        'production_status_id',
        'production_note_id',
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
        'pitch_max',
        'yaw_max',
        'roll_max',
        'x_axis_acceleration',
        'y_axis_acceleration',
        'z_axis_acceleration',
        'chassis_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function description()
    {
        return $this->hasMany('App\Models\Api\StarCitizen\Vehicle\Ship\ShipTranslation');
    }
}
