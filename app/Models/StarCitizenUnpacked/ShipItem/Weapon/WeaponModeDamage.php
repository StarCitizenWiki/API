<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Weapon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponModeDamage extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_weapon_mode_damages';

    protected $fillable = [
        'ship_weapon_mode_id',
        'name',
        'type',
        'damage',
    ];

    protected $casts = [
        'damage' => 'double',
    ];

    public $timestamps = false;

    public function mode(): BelongsTo
    {
        return $this->belongsTo(WeaponMode::class, 'ship_weapon_mode_id', 'id');
    }
}
