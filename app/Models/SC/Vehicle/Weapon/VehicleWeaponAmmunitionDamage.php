<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleWeaponAmmunitionDamage extends Model
{
    use HasFactory;

    protected $table = 'sc_vehicle_weapon_ammunition_damages';

    protected $fillable = [
        'ammunition_id',
        'type',
        'name',
        'damage',
    ];

    protected $casts = [
        'damage' => 'double',
    ];

    /**
     * @return BelongsTo
     */
    public function ammunition(): BelongsTo
    {
        return $this->belongsTo(VehicleWeaponAmmunition::class, 'ammunition_id');
    }
}
