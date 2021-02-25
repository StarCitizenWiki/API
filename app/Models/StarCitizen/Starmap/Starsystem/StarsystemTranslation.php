<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap\Starsystem;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * @return BelongsTo Starsystem
     */
    public function starsystem(): BelongsTo
    {
        return $this->belongsTo(Starsystem::class);
    }
}
