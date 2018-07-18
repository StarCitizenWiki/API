<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\ProductionNote;

use App\Models\AbstractTranslation as Translation;

/**
 * Production Note Translations
 */
class ProductionNoteTranslation extends Translation
{
    protected $fillable = [
        'language_id',
        'production_note_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionNote()
    {
        return $this->belongsTo('App\Models\StarCitizen\ProductionNote\ProductionNote');
    }
}
