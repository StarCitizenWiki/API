<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Thruster extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_thrusters';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'thrust_capacity',
        'min_health_thrust_multiplier',
        'fuel_burn_per_10k_newton',
        'type',
    ];

    protected $casts = [
        'thrust_capacity' => 'double',
        'min_health_thrust_multiplier' => 'double',
        'fuel_burn_per_10k_newton' => 'double',
    ];
}
