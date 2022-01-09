<?php

declare(strict_types=1);

namespace App\Models\Rsi\CommLink;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CommLinkTranslation
 */
class CommLinkTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'comm_link_id',
        'translation',
    ];

    protected $casts = [
        'proofread' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function commLink(): BelongsTo
    {
        return $this->belongsTo(CommLink::class);
    }
}
