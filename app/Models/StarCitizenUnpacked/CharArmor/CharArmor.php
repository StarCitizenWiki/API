<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CommodityItem;
use App\Traits\HasBaseVersionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CharArmor extends CommodityItem
{
    use HasFactory;
    use HasBaseVersionsTrait;

    protected $table = 'star_citizen_unpacked_char_armor';

    protected $fillable = [
        'uuid',
        'armor_type',
        'carrying_capacity',
        'damage_reduction',
        'temp_resistance_min',
        'temp_resistance_max',
        'version',
    ];

    protected $casts = [
        'temp_resistance_min' => 'double',
        'temp_resistance_max' => 'double',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(
            CharArmorAttachment::class,
            'star_citizen_unpacked_char_armor_attachment',
        );
    }

    public function resistances(): HasMany
    {
        return $this->hasMany(
            CharArmorResistance::class,
            'char_armor_id',
        );
    }

    public function helmetParams(): HasOne
    {
        return $this->hasOne(
            CharArmorHelmetParams::class,
            'char_armor_id',
        );
    }
}
