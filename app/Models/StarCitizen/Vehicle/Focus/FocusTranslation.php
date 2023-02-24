<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Focus;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vehicle Focus Translation Model
 */
class FocusTranslation extends Translation
{
    protected $table = 'vehicle_focus_translations';

    protected $fillable = [
        'locale_code',
        'focus_id',
        'translation',
    ];

    /**
     * @return BelongsTo
     */
    public function focus(): BelongsTo
    {
        return $this->belongsTo(Focus::class, 'focus_id');
    }
}
