<?php

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\CommodityItem;
use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ItemPort;
use App\Models\StarCitizenUnpacked\ItemPortLoadout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Optional;

class WeaponPersonal extends CommodityItem
{
    protected $table = 'star_citizen_unpacked_personal_weapons';

    protected $fillable = [
        'uuid',
        'weapon_type',
        'weapon_class',
        'class',
        'effective_range',
        'rof',
        'version',
    ];

    protected $casts = [
        'size' => 'int',
        'magazine_size' => 'int',
        'effective_range' => 'double',
        'rof' => 'double',
    ];

    protected $with = [
//        'modes',
        'item',
       'ammunition',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }

    public function getMagazineTypeAttribute(): string
    {
        $magazineAttach = str_replace(
            $this->name,
            '',
            optional($this->attachments->where('position', 'Magazine Well')->first())->name
        );

        $exploded = explode('(', $magazineAttach);

        return trim($exploded[0]);
    }

    /**
     * @return HasMany
     */
    public function modes(): HasMany
    {
        return $this->hasMany(WeaponPersonalMode::class, 'weapon_id', 'id');
    }

    /**
     * @return Optional
     */
    public function getMagazineAttribute(): Optional
    {
        return optional($this->item->ports()->where('position', 'magazine_well')->first());
    }

    /**
     * @return HasOne
     */
    public function ammunition(): HasOne
    {
        return $this->hasOne(WeaponPersonalAmmunition::class, 'weapon_id', 'id');
    }


    /**
     * @return BelongsToMany
     */
    public function getAttachmentsAttribute()
    {
        return $this->item->ports->map(function(ItemPort $port) {
            return $port->loadout;
        })->filter(function($loadout) {
            return $loadout !== null;
        })->map(function (ItemPortLoadout $loadout) {
            return $loadout->item;
        })->filter();
    }

    /**
     * @return HasManyThrough
     */
    public function damages(): HasManyThrough
    {
        return $this->hasManyThrough(
            WeaponPersonalAmmunitionDamage::class,
            WeaponPersonalAmmunition::class,
            'weapon_id',
            'id'
        );
    }

    public function getBaseModelAttribute(): ?self
    {
        $baseName = preg_replace('/"[\w\s\']+"\s/', '', $this->item->name);
        return self::query()
            ->whereHas('item', function (Builder $query) use ($baseName) {
                $query->where('name', $baseName);
            })
            ->first();
    }
}
