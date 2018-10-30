<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionNote;

use App\Models\System\Translation\AbstractTranslation as Translation;

/**
 * Production Note Translations
 */
class ProductionNoteTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'production_note_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionNote()
    {
        return $this->belongsTo(ProductionNote::class);
    }
}
