<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Focus;

use App\Traits\HasTranslationsTrait as Translations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Vehicle Focus Model
 */
class VehicleFocus extends Model
{
    use VehicleRelations;
    use Translations;

    public $timestamps = false;
    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('App\Models\StarCitizen\Vehicle\Focus\VehicleFocusTranslation');
    }
}
