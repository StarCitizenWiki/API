<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\Starsystem;

use App\Models\System\Translation\AbstractTranslation as Translation;

/**
 * Star System Translation Model
 */
class StarsystemTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'starsystem_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Starsystem
     */
    public function starsystem()
    {
        return $this->belongsTo(Starsystem::class);
    }
}
