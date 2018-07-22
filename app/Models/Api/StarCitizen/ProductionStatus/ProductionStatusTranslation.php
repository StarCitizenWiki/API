<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionStatus;

use App\Models\Api\AbstractTranslation as Translation;

/**
 * Production Status Translations
 */
class ProductionStatusTranslation extends Translation
{
    protected $fillable = [
        'language_id',
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
