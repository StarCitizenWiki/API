<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PowerPlant extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_power_plants';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'power_output',
    ];

    protected $casts = [
        'power_output' => 'double',
    ];
}
