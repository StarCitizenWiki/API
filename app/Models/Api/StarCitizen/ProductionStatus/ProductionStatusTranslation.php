<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionStatus;

use App\Models\System\Translation\AbstractTranslation as Translation;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionStatus()
    {
        return $this->belongsTo(ProductionStatus::class);
    }
}
