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

    protected $casts = [
        'length' => 'float',
        'beam' => 'float',
        'height' => 'float',

        'pitch_max' => 'float',
        'yaw_max' => 'float',
        'roll_max' => 'float',

        'x_axis_acceleration' => 'float',
        'y_axis_acceleration' => 'float',
        'z_axis_acceleration' => 'float',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ShipTranslation::class);
    }
}
