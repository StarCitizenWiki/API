<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Traits\HasTranslationsTrait as Translations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Manufacturer Model
 */
class Manufacturer extends Model
{
    use VehicleRelations;
    use Translations;

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
    ];

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation');
    }
}
