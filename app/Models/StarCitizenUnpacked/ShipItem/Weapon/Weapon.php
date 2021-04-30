<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Weapon extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_weapons';

    protected $with = [
        'modes',
        'damages',
    ];

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'speed',
        'range',
        'size',
        'capacity',
    ];

    protected $casts = [
        'speed' => 'double',
        'range' => 'double',
        'size' => 'double',
        'capacity' => 'double',
    ];

    public function modes(): HasMany
    {
        return $this->hasMany(WeaponMode::class, 'ship_weapon_id');
    }

    public function damages(): HasMany
    {
        return $this->hasMany(WeaponDamage::class, 'ship_weapon_id');
    }
}
