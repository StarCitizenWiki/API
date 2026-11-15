<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\AbstractShipMatrixLookup;

class Focus extends AbstractShipMatrixLookup
{
    public $timestamps = false;

    protected $table = 'shipmatrix_vehicle_foci';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
