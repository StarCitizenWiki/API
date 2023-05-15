<?php

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\CommodityItem;
use App\Models\SC\Item\ItemPort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Optional;

class PersonalWeapon extends CommodityItem
{
    protected $table = 'sc_personal_weapons';

    protected $fillable = [
        'item_uuid',
        'weapon_type',
        'weapon_class',
        'effective_range',
        'rof',
    ];

    protected $casts = [
        'effective_range' => 'double',
        'rof' => 'double',
    ];

    protected $with = [
        'modes',
        'damages',
        'ammunition',
    ];

    public function getRouteKey()
    {
        return $this->item_uuid;
    }

    public function getMagazineTypeAttribute(): string
    {
        $magazineAttach = str_replace(
            $this->name,
            '',
            optional($this->magazine)->name ?? ''
        );

        $exploded = explode('(', $magazineAttach);

        return trim($exploded[0]);
    }

    /**
     * @return HasMany
     */
    public function modes(): HasMany
    {
        return $this->hasMany(PersonalWeaponMode::class, 'weapon_id', 'id');
    }

    /**
     * @return Optional
     */
    public function getMagazineAttribute(): Optional
    {
        $magazine = $this->item->ports()->where('name', 'LIKE', '%magazine%')->first();
        if ($magazine !== null) {
            return optional($magazine->item->specification);
        }

        return optional();
    }

    /**
     * @return HasOne
     */
    public function ammunition(): HasOne
    {
        return $this->hasOne(PersonalWeaponAmmunition::class, 'weapon_id', 'id');
    }


//    /**
//     * @return BelongsToMany
//     */
//    public function getAttachmentsLoadoutAttribute()
//    {
//        return $this->item->ports
//            ->map(function (ItemPort $port) {
//                return $port->item;
//            })
//            ->filter(function ($loadout) {
//                return $loadout !== null;
//            })
//            ->filter();
//    }

    /**
     * @return BelongsToMany
     */
    public function attachments()
    {
        return $this->item->ports();
    }

    /**
     * @return HasManyThrough
     */
    public function damages(): HasManyThrough
    {
        return $this->hasManyThrough(
            PersonalWeaponAmmunitionDamage::class,
            PersonalWeaponAmmunition::class,
            'weapon_id',
            'id'
        );
    }

    public function getBaseModelAttribute(): ?self
    {
        $baseName = preg_replace('/"[\w\s\']+"\s/', '', $this->name);
        return self::query()
            ->whereHas('item', function (Builder $query) use ($baseName) {
                $query->where('name', $baseName);
            })
            ->first();
    }
}
