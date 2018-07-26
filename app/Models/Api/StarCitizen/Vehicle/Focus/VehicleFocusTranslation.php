<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Focus;

use App\Models\Api\Translation\AbstractTranslation as Translation;

/**
 * Vehicle Focus Translation Model
 */
class VehicleFocusTranslation extends Translation
{
    protected $primaryKey = [
        'locale_code',
        'vehicle_focus_id',

    ];
    protected $fillable = [
        'locale_code',
        'vehicle_focus_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleFocus()
    {
        return $this->belongsTo(VehicleFocus::class);
    }
}
