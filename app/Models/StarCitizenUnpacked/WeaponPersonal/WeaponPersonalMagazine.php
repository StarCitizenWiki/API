<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalMagazine extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_magazines';

    protected $fillable = [
        'weapon_id',
        'initial_ammo_count',
        'max_ammo_count',
    ];

    protected $casts = [
        'initial_ammo_count' => 'double',
        'max_ammo_count' => 'double',
    ];

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }
}
