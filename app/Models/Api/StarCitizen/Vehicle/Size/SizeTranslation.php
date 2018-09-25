<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Size;

use App\Models\System\Translation\AbstractTranslation as Translation;

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleSize()
    {
        return $this->belongsTo(Size::class);
    }
}
