<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleWeaponMode extends Model
{
    use HasFactory;

    protected $table = 'sc_vehicle_weapon_modes';

    protected $fillable = [
        'weapon_id',
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

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(VehicleWeapon::class, 'weapon_id');
    }

    public function getDamagePerSecondAttribute(): float
    {
        $multiplier = $this->rounds_per_minute / 60;
        return $this->weapon->ammunition->damage * $multiplier;
    }
}
