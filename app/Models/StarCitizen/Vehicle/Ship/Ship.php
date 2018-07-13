<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Ship;

use App\Traits\HasModelTranslationsTrait as HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ship
 */
class Ship extends Model
{
    use HasTranslations;

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
        'pitch_max',
        'yaw_max',
        'roll_max',
        'xaxis_acceleration',
        'yaxis_acceleration',
        'zaxis_acceleration',
        'chassis_id',
    ];

    protected $with = [
        'manufacturer',
        'vehicle_focus',
        'production_status',
        'vehicle_size',
        'vehicle_type',
        'ships_translations',
    ];

    public function foci()
    {
        return $this->belongsToMany(
            'App\Models\StarCitizen\Vehicle\Focus\VehicleFocus',
            'ship_focus',
            'ship_id',
            'vehicle_focus_id'
        );
    }

    public function manufacturer()
    {
        return $this->hasOne('App\Models\StarCitizen\Manufacturer\Manufacturer');
    }

    public function productionStatus()
    {
        return $this->hasOne('App\Models\StarCitizen\ProductionStatus\ProductionStatus');
    }

    public function type()
    {
        return $this->hasOne('App\Models\StarCitizen\Vehicle\Type\VehicleType');
    }

    public function size()
    {
        return $this->hasOne('App\Models\StarCitizen\Vehicle\VehicleSize');
    }
}
