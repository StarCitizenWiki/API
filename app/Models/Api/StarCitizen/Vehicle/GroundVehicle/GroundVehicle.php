<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ground Vehicle Class
 */
class GroundVehicle extends Vehicle
{
    protected $table = 'vehicles';

    /**
     * Adds the global Ground Vehicle Scope
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            function (Builder $builder) {
                // TODO Refactor to eliminate DB call?
                $type = TypeTranslation::where('translation', 'ground')->first();

                $builder->where('vehicle_type_id', '=', optional($type)->vehicle_type_id);
            }
        );
    }
}
