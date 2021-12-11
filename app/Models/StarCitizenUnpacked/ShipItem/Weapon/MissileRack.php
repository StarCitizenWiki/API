<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MissileRack extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_missile_racks';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'missile_count',
        'missile_size',
    ];

    protected $casts = [
        'missile_count' => 'int',
        'missile_size' => 'int',
    ];
}
