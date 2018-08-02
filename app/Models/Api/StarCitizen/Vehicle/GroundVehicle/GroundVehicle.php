<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\StarCitizen\Vehicle\Type\VehicleTypeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle as Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ground Vehicle Class
 */
class GroundVehicle extends Vehicle
{
    protected $table = 'vehicles';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('type', function (Builder $builder) {
            $type = VehicleTypeTranslation::where('translation', 'ground')->first();
            $builder->where('vehicle_type_id', '=', $type->vehicle_type_id);
        });
    }
}
