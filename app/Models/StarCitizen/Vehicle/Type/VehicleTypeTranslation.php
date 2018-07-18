<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Type;

use App\Models\AbstractTranslation as Translation;

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
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Type\VehicleType');
    }
}
