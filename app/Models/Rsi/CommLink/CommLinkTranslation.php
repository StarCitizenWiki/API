<?php declare(strict_types = 1);

namespace App\Models\Rsi\CommLink;

use App\Models\System\Translation\AbstractTranslation as Translation;

class CommLinkTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'comm_link_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commLink()
    {
        return $this->belongsTo(CommLink::class);
    }

    /**
     * Associate Translations with Parent Model
     *
     * @return string
     */
    public function getMorphClass()
    {
        return CommLink::class;
    }
}
