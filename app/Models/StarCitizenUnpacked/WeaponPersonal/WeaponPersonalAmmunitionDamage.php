<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalAmmunitionDamage extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_ammunition_damages';

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
        return $this->belongsTo(WeaponPersonalAmmunition::class, 'ammunition_id');
    }
}
