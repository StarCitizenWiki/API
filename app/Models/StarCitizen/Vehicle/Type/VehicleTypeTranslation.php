<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Type;

use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle Type Translations Model
 */
class VehicleTypeTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'vehicle_type_id',
        'type',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleType()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Type\VehicleType');
    }
}
