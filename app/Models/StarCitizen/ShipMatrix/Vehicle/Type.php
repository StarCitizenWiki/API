<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Models\StarCitizen\ShipMatrix\AbstractShipMatrixLookup;

class Type extends AbstractShipMatrixLookup
{
    protected $table = 'shipmatrix_vehicle_types';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
