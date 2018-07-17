<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\GroundVehicle;

use Illuminate\Database\Eloquent\Model;

/**
 * Ground Vehicle Translation Model
 */
class GroundVehicleTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'ground_vehicle_id',
        'description',
        'production_note',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groundVehicle()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle');
    }
}
