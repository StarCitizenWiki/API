<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Focus;

use App\Models\System\Translation\AbstractTranslation as Translation;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function focus()
    {
        return $this->belongsTo(Focus::class, 'focus_id');
    }
}
