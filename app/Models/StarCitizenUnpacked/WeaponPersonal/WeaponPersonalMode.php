<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalMode extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_modes';

    protected $fillable = [
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
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }

    public function getDamagePerSecondAttribute(): float
    {
        $multiplier = $this->rounds_per_minute / 60;
        return $this->weapon->ammunition->damage * $multiplier;
    }
}
