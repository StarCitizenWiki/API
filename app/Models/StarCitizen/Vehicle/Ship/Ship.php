<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Ship;

use App\Traits\HasModelTranslationsTrait as HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ship
 */
class Ship extends Model
{
    use HasTranslations;

    protected $with = [
        'manufacturer',
        'vehicle_focus',
        'vehicle_production_status',
        'vehicle_size',
        'vehicle_type',
        'ships_translations',
    ];

    public function foci()
    {
        return $this->belongsToMany(
            'App\Models\StarCitizen\Vehicle\Focus\VehicleFocus',
            'ship_focus',
            'ship_id',
            'vehicle_focus_id'
        );
    }
}
