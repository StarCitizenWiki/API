<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle;

use App\Traits\HasModelTranslationsTrait as HasTranslations;
use Illuminate\Database\Eloquent\Model;

class VehicleFocus extends Model
{
    use HasTranslations;

    protected $with = [
        'vehicle_foci_translations',
    ];

    public function ships()
    {
        return $this->belongsToMany(
            'App\Models\StarCitizen\Vehicle\Ship\Ship',
            'ship_focus',
            'vehicle_focus_id',
            'ship_id'
        );
    }
}
