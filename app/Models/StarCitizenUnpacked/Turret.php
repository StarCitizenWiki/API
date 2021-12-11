<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Turret extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_turrets';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'max_mounts',
        'min_size',
        'max_size',
    ];

    protected $casts = [
        'max_mounts' => 'int',
        'min_size' => 'int',
        'max_size' => 'int',
    ];
}
