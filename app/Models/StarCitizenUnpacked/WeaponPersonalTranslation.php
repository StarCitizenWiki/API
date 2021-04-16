<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\System\Translation\AbstractTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalTranslation extends AbstractTranslation
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_translations';

    protected $fillable = [
        'locale_code',
        'weapon_id',
        'translation',
    ];

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }
}
