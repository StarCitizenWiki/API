<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ProductionStatus;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Production Status Translations
 */
class ProductionStatusTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'production_status_id',
        'translation',
    ];

    /**
     * @return BelongsTo
     */
    public function productionStatus(): BelongsTo
    {
        return $this->belongsTo(ProductionStatus::class);
    }
}
