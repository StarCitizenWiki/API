<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class WeaponMode extends Model
{
    use HasFactory;

    protected $with = [
        'damages',
    ];

    protected $table = 'star_citizen_unpacked_ship_weapon_modes';

    protected $fillable = [
        'ship_weapon_id',
        'mode',
        'localised',
        'type',
        'rounds_per_minute',
        'ammo_per_shot',
        'pellets_per_shot',
    ];

    protected $casts = [
        'rounds_per_minute' => 'double',
        'ammo_per_shot' => 'double',
        'pellets_per_shot' => 'double',
    ];

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class, 'ship_weapon_id', 'id');
    }

    /**
     * @return HasManyThrough
     */
    public function damages(): HasManyThrough
    {
        return $this->hasManyThrough(
            WeaponDamage::class,
            Weapon::class,
            'id',
            'ship_weapon_id',
        );
    }

    public function getDamagePerSecondAttribute(): float
    {
        $multiplier = $this->rounds_per_minute / 60;
        return $this->weapon->damage * $multiplier;
    }
}
