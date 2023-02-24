<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalInventory extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_personal_inventories';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'scu',
    ];

    protected $casts = [
        'scu' => 'double',
    ];
}
