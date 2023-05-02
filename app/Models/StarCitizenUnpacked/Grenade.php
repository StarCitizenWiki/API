<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grenade extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_grenades';

    protected $fillable = [
        'uuid',
        'aoe',
        'damage_type',
        'damage',
    ];

    protected $casts = [
        'aoe' => 'double',
        'damage' => 'double',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }
}
