<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelTank extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_fuel_tanks';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'fill_rate',
        'drain_rate',
        'capacity',
    ];

    protected $casts = [
        'fill_rate' => 'double',
        'drain_rate' => 'double',
        'capacity' => 'double',
    ];
}
