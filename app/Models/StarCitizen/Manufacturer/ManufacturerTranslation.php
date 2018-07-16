<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Manufacturer;

use Illuminate\Database\Eloquent\Model;

/**
 * Manufacturer Translations
 */
class ManufacturerTranslation extends Model
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
        return $this->belongsTo('App\Models\StarCitizen\Manufacturer\Manufacturer');
    }
}
