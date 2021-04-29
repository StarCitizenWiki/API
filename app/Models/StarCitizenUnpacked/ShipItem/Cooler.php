<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cooler extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_coolers';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'cooling_rate',
    ];

    protected $casts = [
        'cooling_rate' => 'double',
    ];
}
