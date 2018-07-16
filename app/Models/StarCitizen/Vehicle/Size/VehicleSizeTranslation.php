<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Size;

use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle Size Translations Model
 */
class VehicleSizeTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'vehicle_size_id',
        'size',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleSize()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Size\VehicleSize');
    }
}
