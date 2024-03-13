<?php

namespace App\Models\SC\Ammunition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ammunition extends Model
{
    use HasFactory;

    protected $table = 'sc_ammunitions';

    protected $fillable = [
        'uuid',
        'size',
        'lifetime',
        'speed',
        'range',
    ];

    protected $casts = [
        'size' => 'integer',
        'lifetime' => 'double',
        'speed' => 'double',
        'range' => 'double',
    ];

    public function damageFalloffs(): HasMany
    {
        return $this->hasMany(AmmunitionDamageFalloff::class, 'ammunition_uuid', 'uuid');
    }

    public function piercability(): HasOne
    {
        return $this->hasOne(AmmunitionPiercability::class, 'ammunition_uuid', 'uuid');

    }

    public function damages(): HasMany
    {
        return $this->hasMany(AmmunitionDamage::class, 'ammunition_uuid', 'uuid');
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
    }
}
