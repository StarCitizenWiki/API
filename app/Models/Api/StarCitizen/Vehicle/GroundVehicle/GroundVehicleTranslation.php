<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\AbstractTranslation as Translation;

/**
 * Ground Vehicle Translation Model
 */
class GroundVehicleTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'ground_vehicle_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groundVehicle()
    {
        return $this->belongsTo(GroundVehicle::class);
    }
}
