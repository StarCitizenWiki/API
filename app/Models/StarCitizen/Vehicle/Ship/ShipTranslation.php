<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Ship;

use Illuminate\Database\Eloquent\Model;

/**
 * Ship Translations Model
 */
class ShipTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'ship_id',
        'description',
        'production_note',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Ships
     */
    public function ship()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Ship\Ship');
    }
}
