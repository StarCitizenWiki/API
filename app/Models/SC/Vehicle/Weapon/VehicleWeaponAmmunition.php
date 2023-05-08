<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleWeaponAmmunition extends Model
{
    use HasFactory;

    protected $table = 'sc_vehicle_weapon_ammunitions';

    protected $fillable = [
        'weapon_id',
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

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(VehicleWeapon::class, 'weapon_id');
    }

    public function damages(): HasMany
    {
        return $this->hasMany(VehicleWeaponAmmunitionDamage::class, 'ammunition_id');
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
    }
}
