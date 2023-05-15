<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarrelAttach extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_barrel_attachs';

    protected $fillable = [
        'item_uuid',
        'type',
    ];

    public function getRouteKey()
    {
        return $this->item_uuid;
    }
}
