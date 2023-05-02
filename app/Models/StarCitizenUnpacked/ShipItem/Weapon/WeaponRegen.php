<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponRegen extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_weapon_regens';

    protected $fillable = [
        'ship_weapon_id',
        'requested_regen_per_sec',
        'requested_ammo_load',
        'cooldown',
        'cost_per_bullet',
    ];

    protected $casts = [
        'requested_regen_per_sec' => 'double',
        'requested_ammo_load' => 'double',
        'cooldown' => 'double',
        'cost_per_bullet' => 'double',
    ];

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class, 'ship_weapon_id', 'id');
    }
}
