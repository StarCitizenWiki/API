<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelIntake extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_fuel_intakes';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'fuel_push_rate',
        'minimum_rate',
    ];

    protected $casts = [
        'fuel_push_rate' => 'double',
        'minimum_rate' => 'double',
    ];
}
