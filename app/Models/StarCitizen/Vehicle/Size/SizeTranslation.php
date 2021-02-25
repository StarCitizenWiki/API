<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Size;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vehicle Size Translations Model
 */
class SizeTranslation extends Translation
{
    protected $table = 'vehicle_size_translations';

    protected $fillable = [
        'locale_code',
        'size_id',
        'translation',
    ];

    /**
     * @return BelongsTo
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}
