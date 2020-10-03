<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\ProductionNote;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * @return BelongsTo
     */
    public function productionNote(): BelongsTo
    {
        return $this->belongsTo(ProductionNote::class);
    }
}
