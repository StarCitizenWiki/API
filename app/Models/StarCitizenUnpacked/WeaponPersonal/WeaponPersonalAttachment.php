<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WeaponPersonalAttachment extends Pivot
{
    protected $table = 'star_citizen_unpacked_personal_weapon_attachment';

    protected $fillable = [
        'weapon_id',
        'attachment_id',
    ];

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }

    /**
     * @return BelongsTo
     */
    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}
