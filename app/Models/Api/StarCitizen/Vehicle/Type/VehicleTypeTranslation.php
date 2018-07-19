<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Type;

use App\Models\Api\AbstractTranslation as Translation;

/**
 * Vehicle Type Translations Model
 */
class VehicleTypeTranslation extends Translation
{
    protected $fillable = [
        'language_id',
        'vehicle_type_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleType()
    {
        return $this->belongsTo('App\Models\Api\StarCitizen\Vehicle\Type\VehicleType');
    }
}
