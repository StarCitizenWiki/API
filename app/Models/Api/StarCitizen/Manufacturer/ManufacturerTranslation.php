<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Models\Api\AbstractTranslation as Translation;

/**
 * Manufacturer Translations
 */
class ManufacturerTranslation extends Translation
{
    protected $fillable = [
        'language_id',
        'manufacturer_id',
        'known_for',
        'description',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
}
