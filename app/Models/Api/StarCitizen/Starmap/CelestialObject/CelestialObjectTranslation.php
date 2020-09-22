<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Models\System\Translation\AbstractTranslation as Translation;

/**
 * Class CelestialObjectTranslation
 */
class CelestialObjectTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'celestial_object_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo CelestialObject
     */
    public function celestialObject()
    {
        return $this->belongsTo(CelestialObject::class);
    }
}
