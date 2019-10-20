<?php

declare(strict_types=1);

namespace App\Models\Transcript;

use App\Models\System\Translation\AbstractTranslation as Translation;

class TranscriptTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'comm_link_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transcript(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Transcript::class);
    }
}
