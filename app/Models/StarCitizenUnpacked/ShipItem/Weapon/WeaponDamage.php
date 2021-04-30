<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponDamage extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_weapon_damages';

    protected $fillable = [
        'ship_weapon_id',
        'type',
        'name',

        'damage',
    ];

    protected $casts = [
        'damage' => 'double',
    ];

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class, 'ship_weapon_id', 'id');
    }
}
