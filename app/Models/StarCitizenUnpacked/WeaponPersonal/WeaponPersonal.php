<?php

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeaponPersonal extends CommodityItem
{
    protected $table = 'star_citizen_unpacked_personal_weapons';

    protected $fillable = [
        'uuid',
        'weapon_class',
        'class',
        'magazine_size',
        'effective_range',
        'rof',
        'attachment_size_optics',
        'attachment_size_barrel',
        'attachment_size_underbarrel',
        'ammunition_speed',
        'ammunition_range',
        'ammunition_damage',
    ];

    protected $casts = [
        'size' => 'int',
        'magazine_size' => 'int',
        'effective_range',
        'ammunition_speed' => 'double',
        'ammunition_range' => 'double',
        'ammunition_damage' => 'double',
    ];

    protected $with = [
        'modes',
        'item'
    ];

    /**
     * @return HasMany
     */
    public function modes(): HasMany
    {
        return $this->hasMany(WeaponPersonalMode::class, 'weapon_id', 'id');
    }
}
