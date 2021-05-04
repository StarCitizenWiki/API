<?php

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'modes',
        'item',
        'magazine',
        'ammunition',
        'attachmentPorts',
        'attachments',
    ];

    public function getMagazineTypeAttribute(): string
    {
        $magazineAttach = str_replace(
            $this->name,
            '',
            $this->attachments()->where('position', 'magazine')->first()->name
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
     * @return HasOne
     */
    public function magazine(): HasOne
    {
        return $this->hasOne(WeaponPersonalMagazine::class, 'weapon_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function ammunition(): HasOne
    {
        return $this->hasOne(WeaponPersonalAmmunition::class, 'weapon_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function attachmentPorts(): HasMany
    {
        return $this->hasMany(WeaponPersonalAttachmentPort::class, 'weapon_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(WeaponPersonalAttachment::class, 'weapon_id', 'id');
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
}
