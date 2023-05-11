<?php

namespace App\Models\SC\Vehicle\Weapon;

use App\Models\SC\CommodityItem;
use App\Models\SC\Item\ItemPort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Optional;

class VehicleWeapon extends CommodityItem
{
    protected $table = 'sc_vehicle_weapons';

    protected $fillable = [
        'item_uuid',
        'weapon_type',
        'weapon_class',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'double',
    ];

    protected $with = [
        'modes',
        'item',
        'ammunition',
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
}
