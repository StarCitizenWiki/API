<?php

namespace App\Models\SC\Vehicle\Weapon;

use App\Models\SC\Ammunition\Ammunition;
use App\Models\SC\CommodityItem;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleWeapon extends CommodityItem
{
    use HasDescriptionDataTrait;

    protected $table = 'sc_vehicle_weapons';

    protected $fillable = [
        'item_uuid',
        'capacity',
        'ammunition_uuid',
    ];

    protected $casts = [
        'capacity' => 'double',
    ];

    protected $with = [
        'modes',
        'item',
        'regen',
    ];

    public function getRouteKey()
    {
        return $this->item_uuid;
    }

    public function modes(): HasMany
    {
        return $this->hasMany(VehicleWeaponMode::class, 'weapon_id');
    }

    public function ammunition(): HasOne
    {
        return $this->hasOne(Ammunition::class, 'uuid', 'ammunition_uuid')->withDefault();
    }

    public function regen(): HasOne
    {
        return $this->hasOne(VehicleWeaponRegeneration::class, 'weapon_id');
    }

    public function damages()
    {
        return $this->ammunition->damages;
    }

    public function getDamageAttribute(): float
    {
        return $this->ammunition->damage;
    }


    public function getWeaponClassAttribute()
    {
        return $this->getDescriptionDatum('Class');
    }

    public function getWeaponTypeAttribute()
    {
        return $this->getDescriptionDatum('Item Type');
    }
}
