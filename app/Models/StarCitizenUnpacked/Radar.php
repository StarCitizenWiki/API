<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Radar extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_radars';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'detection_lifetime',
        'altitude_ceiling',
        'enable_cross_section_occlusion',
    ];

    protected $casts = [
        'detection_lifetime' => 'double',
        'altitude_ceiling' => 'double',
        'enable_cross_section_occlusion' => 'boolean',
    ];
}
