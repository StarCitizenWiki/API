<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Vehicle;

use App\Models\Api\Translation\AbstractTranslation as Translation;

/**
 * Ship Translations Model
 */
class VehicleTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'vehicle_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Ships
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Associate Translations with the Vehicle
     *
     * @return string
     */
    public function getMorphClass()
    {
        return Vehicle::class;
    }
}