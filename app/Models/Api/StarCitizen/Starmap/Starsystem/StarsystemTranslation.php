<?php
/**
 * User: Keonie
 * Date: 04.08.2018 19:55
 */

namespace App\Models\Api\StarCitizen\Starmap\Starsystem;

use App\Models\Api\Translation\AbstractTranslation as Translation;

/**
 * Star System Translation Model
 * @package App\Models\Api\StarCitizen\Starmap\Starsystem
 */
class StarsystemTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'cig_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Starsystem
     */
    public function starsystem()
    {
        return $this->belongsTo(Starsystem::class);
    }

    /**
     * Associate Translations with the Starsystem
     *
     * @return string
     */
    public function getMorphClass()
    {
        return Starsystem::class;
    }
}