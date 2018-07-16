<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Focus;

use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle Focus Translation Model
 */
class VehicleFocusTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'vehicle_focus_id',
        'focus',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleFocus()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Focus\VehicleFocus');
    }
}
