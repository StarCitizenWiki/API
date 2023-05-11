<?php

declare(strict_types=1);

namespace App\Models\SC\Char\Clothing;

use App\Models\SC\CommodityItem;
use App\Traits\HasBaseVersionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clothing extends CommodityItem
{
    use HasFactory;
    use HasBaseVersionsTrait;

    protected $table = 'sc_clothes';

    protected $fillable = [
        'item_uuid',
        'type',
    ];

    protected $with = [
        'resistances'
    ];

    public function getRouteKey()
    {
        return $this->item_uuid;
    }

    public function resistances(): HasMany
    {
        return $this->hasMany(
            ClothingResistance::class,
            'clothing_id',
        );
    }

    public function damageResistances(): HasMany
    {
        return $this->resistances()->whereNotIn('type', ['temp_min', 'temp_max', 'damage_reduction']);
    }

    public function getTempResistanceMinAttribute()
    {
        return $this->resistances()
            ->where('type', 'temp_min')
            ->first()
            ->threshold;
    }

    public function getTempResistanceMaxAttribute()
    {
        return $this->resistances()
            ->where('type', 'temp_max')
            ->first()
            ->threshold;
    }

    public function getDamageReductionAttribute()
    {
        return $this->resistances()
            ->where('type', 'damage_reduction')
            ->first()
            ->multiplier ?? null;
    }
}
