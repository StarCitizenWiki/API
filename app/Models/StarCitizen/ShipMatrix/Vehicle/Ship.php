<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use Illuminate\Database\Eloquent\Builder;

class Ship extends Vehicle
{
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(
            'size',
            function (Builder $builder) {
                $builder->has('ships');
            }
        );
    }
}
