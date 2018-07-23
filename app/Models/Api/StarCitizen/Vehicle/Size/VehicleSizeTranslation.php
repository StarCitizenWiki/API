<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Size;

use App\Models\Api\AbstractTranslation as Translation;

/**
 * Vehicle Size Translations Model
 */
class VehicleSizeTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'vehicle_size_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleSize()
    {
        return $this->belongsTo(VehicleSize::class);
    }
}
