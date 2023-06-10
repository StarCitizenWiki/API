<?php

namespace App\Models\SC\Vehicle\Weapon;

use App\Models\SC\CommodityItem;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleWeapon extends CommodityItem
{
    use HasDescriptionDataTrait;

    protected $table = 'sc_vehicle_weapons';

    protected $fillable = [
        'item_uuid',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'double',
    ];

    protected $with = [
        'modes',
        'item',
        'ammunition',
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
        return $this->hasOne(VehicleWeaponAmmunition::class, 'weapon_id');
    }

    public function regen(): HasOne
    {
        return $this->hasOne(VehicleWeaponRegeneration::class, 'weapon_id');
    }

    /**
     * @return HasManyThrough
     */
    public function damages(): HasManyThrough
    {
        return $this->hasManyThrough(
            VehicleWeaponAmmunitionDamage::class,
            VehicleWeaponAmmunition::class,
            'weapon_id',
            'id'
        );
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
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
