<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\ProductionNote;

use Illuminate\Database\Eloquent\Model;

/**
 * Production Note Translations
 */
class ProductionNoteTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'production_note_id',
        'production_note',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionNote()
    {
        return $this->belongsTo('App\Models\StarCitizen\ProductionNote\ProductionNote');
    }
}
