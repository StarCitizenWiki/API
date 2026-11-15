<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\AbstractShipMatrixLookup;
use Illuminate\Database\Eloquent\Builder;

/**
 * Vehicle Size Model
 */
class Size extends AbstractShipMatrixLookup
{
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $table = 'shipmatrix_vehicle_sizes';

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Ships
     */
    public function scopeShip(Builder $query): void
    {
        $query->where('slug', '!=', 'vehicle');
    }

    /**
     * Ground Vehicles
     */
    public function scopeGroundVehicle(Builder $query): void
    {
        $query->where('slug', 'vehicle');
    }
}
