<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Type;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Vehicle Type Translations Model
 */
class TypeTranslation extends Translation
{
    protected $table = 'vehicle_type_translations';

    protected $fillable = [
        'locale_code',
        'type_id',
        'translation',
    ];

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
