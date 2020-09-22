<?php

declare(strict_types=1);

namespace App\Models\Transcript;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TranscriptTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'comm_link_id',
        'translation',
    ];

    /**
     * @return BelongsTo
     */
    public function transcript(): BelongsTo
    {
        return $this->belongsTo(Transcript::class);
    }
}
