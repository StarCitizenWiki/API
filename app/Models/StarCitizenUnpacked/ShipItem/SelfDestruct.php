<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SelfDestruct extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_self_destructs';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'damage',
        'radius',
        'min_radius',
        'phys_radius',
        'min_phys_radius',
        'time',
    ];

    protected $casts = [
        'damage' => 'double',
        'radius' => 'double',
        'min_radius' => 'double',
        'phys_radius' => 'double',
        'min_phys_radius' => 'double',
        'time' => 'double',
    ];
}
