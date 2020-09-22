<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Vehicle\Vehicle;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VehicleComponent extends Pivot
{
    protected $fillable = [
        'mounts',
        'size',
        'details',
        'quantity',
    ];
}
