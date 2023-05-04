<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalWeaponOptics extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_personal_weapon_optics';

    protected $fillable = [
        'item_uuid',
        'magnification',
        'type',
    ];

    public function getRouteKey()
    {
        return $this->item_uuid;
    }
}
