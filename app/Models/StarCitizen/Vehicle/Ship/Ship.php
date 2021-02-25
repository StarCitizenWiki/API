<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Ship;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ship Model
 */
class Ship extends Vehicle
{
    protected $table = 'vehicles';

    /**
     * Adds the global Ship Scope
     */
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
