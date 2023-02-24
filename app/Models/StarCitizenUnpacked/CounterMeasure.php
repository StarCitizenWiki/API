<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounterMeasure extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_counter_measures';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'initial_ammo_count',
        'max_ammo_count',
    ];

    protected $casts = [
        'initial_ammo_count' => 'double',
        'max_ammo_count' => 'double',
    ];
}
