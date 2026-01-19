<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VehicleComponent extends Pivot
{
    protected $table = 'shipmatrix_vehicle_components';

    protected $fillable = [
        'mounts',
        'size',
        'details',
        'quantity',
    ];
}
