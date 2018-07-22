<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Ship;

use App\Models\Api\AbstractTranslation as Translation;

/**
 * Ship Translations Model
 */
class ShipTranslation extends Translation
{
    protected $fillable = [
        'language_id',
        'ship_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Ships
     */
    public function ship()
    {
        return $this->belongsTo(Ship::class);
    }
}
