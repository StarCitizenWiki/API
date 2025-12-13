<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
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
    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'size',
            static function (Builder $builder) {
                $builder->has('groundVehicles');
            }
        );
    }
}
