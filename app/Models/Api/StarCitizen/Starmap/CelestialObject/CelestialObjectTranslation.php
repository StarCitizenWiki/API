<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     * @return BelongsTo CelestialObject
     */
    public function celestialObject(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class);
    }
}
