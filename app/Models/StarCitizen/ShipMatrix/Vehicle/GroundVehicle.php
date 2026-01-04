<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use Illuminate\Database\Eloquent\Builder;

class GroundVehicle extends Vehicle
{
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
