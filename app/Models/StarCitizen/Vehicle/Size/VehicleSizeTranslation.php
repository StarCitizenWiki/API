<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Size;

use App\Models\AbstractTranslation as Translation;

/**
 * Vehicle Size Translations Model
 */
class VehicleSizeTranslation extends Translation
{
    protected $fillable = [
        'language_id',
        'vehicle_size_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleSize()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Size\VehicleSize');
    }
}
