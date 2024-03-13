<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\Ammunition\Ammunition;
use App\Models\SC\CommodityItem;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PersonalWeaponMagazine extends CommodityItem
{
    use HasDescriptionDataTrait;
    use HasFactory;

    protected $table = 'sc_item_personal_weapon_magazines';

    protected $fillable = [
        'item_uuid',
        'initial_ammo_count',
        'max_ammo_count',
        'ammunition_uuid',
    ];

    protected $casts = [
        'initial_ammo_count' => 'double',
        'max_ammo_count' => 'double',
    ];

    public function ammunition(): HasOne
    {
        return $this->hasOne(Ammunition::class, 'uuid', 'ammunition_uuid');
    }
}
