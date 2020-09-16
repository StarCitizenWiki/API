<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Manufacturer Translations
 */
class ManufacturerTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'manufacturer_id',
        'known_for',
        'description',
    ];

    /**
     * @return BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
