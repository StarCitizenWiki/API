<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Missile extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_missiles';

    protected $with = [
        'damages',
    ];

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'signal_type',
        'lock_time',
    ];

    protected $casts = [
        'lock_time' => 'double',
    ];

    public function damages(): HasMany
    {
        return $this->hasMany(MissileDamage::class, 'ship_missile_id');
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
    }
}
