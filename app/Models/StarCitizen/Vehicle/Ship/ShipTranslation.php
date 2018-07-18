<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Ship;

use App\Models\AbstractTranslation as Translation;

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
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Ship\Ship');
    }
}
