<?php
/**
 * User: Keonie
 * Date: 04.08.2018 20:12
 */

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Models\Api\Translation\AbstractTranslation as Translation;

/**
 * Class CelestialObjectTranslation
 * @package App\Models\Api\StarCitizen\Starmap\CelestialObject
 */
class CelestialObjectTranslation extends Translation
{
    protected $fillable = [
        'id',
        'locale_code',
        'cig_id',
        'translation',
    ];

    protected $table = 'celestial_object_translation';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo CelestialObject
     */
    public function celestialObject()
    {
        return $this->belongsTo(CelestialObject::class);
    }

    /**
     * Associate Translations with the Celestial Object
     *
     * @return string
     */
    public function getMorphClass()
    {
        return CelestialObject::class;
    }
}